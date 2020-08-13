<?php
/**
 * Total Cookie Consent plugin for Craft CMS 3.x
 *
 * The plugin provides total control over the cookie consent collection process and includes three consent collection options: No Consent, Implied Consent, and Explicit Consent. Collection methods can be tailored per country or state to provide an optimal non-intrusive user experience.
 *
 * @link      https://www.page.works/
 * @copyright Copyright (c) 2020 Pageworks
 */

namespace page8\totalcookieconsent\services;

use page8\totalcookieconsent\TotalCookieConsent;

use Craft;
use craft\base\Component;
use craft\web\View;

/**
 * @author    Pageworks
 * @package   TotalCookieConsent
 * @since     1.0.0
 */
class TotalCookieConsentService extends Component
{
    // Public Methods
    // =========================================================================

    public function lookupVisitorInfo()
    {
        $devMode = Craft::$app->getConfig()->general->devMode;
        $localIps = ['127.0.0.1', '::1'];
        $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
        if (in_array($ip, $localIps) && $devMode)
        {
            return [];
        }

        $ret = [];
        $cachedData = Craft::$app->getCache()->get('tcc.' . $ip);
        if ($cachedData) {
            $ret = json_decode($cachedData, true);
        }
        return $ret;
    }

    public function acceptImpliedCookies()
    {
        $devMode = Craft::$app->getConfig()->general->devMode;
        $localIps = ['127.0.0.1', '::1'];
        $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
        if (in_array($ip, $localIps) && $devMode)
        {
            return;
        }

        $cachedData = Craft::$app->getCache()->get('tcc.' . $ip);
        $settings = TotalCookieConsent::getInstance()->settings;
        $visitorInfo = $this->lookupVisitorInfo();
        foreach ($settings->consentTypes as $type)
        {
            $visitorInfo['consent'][$type['handle']] = true;
        }
        Craft::$app->getCache()->set('tcc.' . $ip, json_encode($visitorInfo), 86400);
        return;
    }

    public function getConsentResponse()
    {
        $ret = [];
        $settings = TotalCookieConsent::getInstance()->settings;

        $devMode = Craft::$app->getConfig()->general->devMode;
        $localIps = ['127.0.0.1', '::1'];
        $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
        if (in_array($ip, $localIps) && $devMode)
        {
            foreach ($settings->consentTypes as $type)
            {
                $ret[$type['handle']] = true;
            }
            return $ret;
        }

        $visitorInfo = $this->lookupVisitorInfo();
        if (isset($visitorInfo['consent'])) {
            foreach ($visitorInfo['consent'] as $key => $value)
            {
                $ret[$key] = $value;
            }
        }
        else
        {
            foreach ($settings->consentTypes as $type)
            {
                $ret[$type['handle']] = false;
            }
        }

        return $ret;
    }

    public function save(string $ip, array $params)
    {
        $devMode = Craft::$app->getConfig()->general->devMode;
        $localIps = ['127.0.0.1', '::1'];
        if (in_array($ip, $localIps) && $devMode)
        {
            return;
        }

        $visitorInfo = $this->lookupVisitorInfo();
        $settings = TotalCookieConsent::getInstance()->settings;
        $visitorInfo['consent'] = [];

        foreach ($settings->consentTypes as $type)
        {
            if (isset($params[$type['handle']]))
            {
                $visitorInfo['consent'][$type['handle']] = $params[$type['handle']];
            }
            else
            {
                $value = 0;
                if ($type['required'])
                {
                    // The user must have unchecked the box using some HTML editing tomfoolery
                    $value = 1;
                }
                $visitorInfo['consent'][$type['handle']] = $value;
            }
        }

        Craft::$app->getCache()->set('tcc.' . $ip, json_encode($visitorInfo), 86400);
    }

    public function getBannerType(string $defaultBanner, bool $gdpr, string $visitorsCountry, string $visitorsRegion, array $countries, array $regions) : string
    {
        $bannerType = $defaultBanner;

        if ($gdpr)
        {
            $euCountries = [
                'AT',
                'BE',
                'BG',
                'HR',
                'CY',
                'CZ',
                'DK',
                'EE',
                'FI',
                'FR',
                'DE',
                'GR',
                'HU',
                'IE',
                'IT',
                'LV',
                'LT',
                'LU',
                'MT',
                'NL',
                'PL',
                'PT',
                'RO',
                'SK',
                'SI',
                'ES',
                'SE',
            ];
            foreach ($euCountries as $country)
            {
                if ($country == $visitorsCountry)
                {
                    return 'explicit';
                }
            }
        }
        
        foreach ($countries as $country)
        {
            if ($country['countryCode'] == $visitorsCountry)
            {
                return $country['bannerType'];
            }
        }

        foreach ($regions as $region)
        {
            if ($region['countryCode'] == $visitorsCountry && $region['regionCode'] == $visitorsRegion)
            {
                return $region['bannerType'];
            }
        }

        return $defaultBanner;
    }

    public function locateVisitor(string $apiKey) : array
    {
        $devMode = Craft::$app->getConfig()->general->devMode;
        $localIps = ['127.0.0.1', '::1'];
        $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
        if (in_array($ip, $localIps) && $devMode)
        {
            return [];
        }

        $visitorInfo = $this->lookupVisitorInfo();
        if (!empty($visitorInfo) && isset($visitorInfo['country']) && isset($visitorInfo['region'])) {
            return $visitorInfo;
        }

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', 'http://api.ipapi.com/' . $ip . '?access_key=' . $apiKey);
            $data = json_decode($response->getBody(), true);
            $visitorInfo = [
                'country' => $data['country_code'],
                'region' => $data['region_code'],
            ]; 
            Craft::$app->getCache()->add('tcc.' . $ip, json_encode($visitorInfo), 86400);
            return $visitorInfo;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function renderBanner()
    {
        $settings = TotalCookieConsent::getInstance()->settings;

        $bannerType = $settings->defaultConsentType;

        $visitorInfo = null;
        if (!empty($settings->ipapiKey))
        {
            $visitorInfo = $this->locateVisitor($settings->ipapiKey);
            if (!empty($visitorInfo))
            {
                $countries = [];
                if (!empty($settings->countriesTable))
                {
                    $countries = $settings->countriesTable;
                }
                $regions = [];
                if (!empty($settings->regionsTable))
                {
                    $regions = $settings->regionsTable;
                }
                $bannerType = $this->getBannerType($settings->defaultConsentType, $settings->gdprBanner, $visitorInfo['country'], $visitorInfo['region'], $countries, $regions);
            }
        }
        else
        {
            $visitorInfo = $this->lookupVisitorInfo();
        }

        $template = null;
        $view = Craft::$app->getView();
        $oldMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        if (!isset($visitorInfo['consent']))
        {
            switch ($bannerType)
            {
                case 'implied':
                    $view->registerAssetBundle('page8\\totalcookieconsent\\assetbundles\\totalcookieconsent\\ImpliedConsentBannerAsset');
                    $template = $view->renderTemplate('total-cookie-consent/banners/implied', [
                        'copy' => $settings->impliedCopy,
                        'url' => $settings->cookiePolicyLink,
                    ]);
                    $this->acceptImpliedCookies();
                    break;
                case 'explicit':
                    $view->registerAssetBundle('page8\\totalcookieconsent\\assetbundles\\totalcookieconsent\\ExplicitConsentBannerAsset');
                    $template = $view->renderTemplate('total-cookie-consent/banners/explicit', [
                        'consentTypes' => $settings->consentTypes,
                    ]);
                    break;
                default:
                    $this->acceptImpliedCookies();
                    break;
            }
        }
        $view->setTemplateMode($oldMode);
        return $template;
    }
}
