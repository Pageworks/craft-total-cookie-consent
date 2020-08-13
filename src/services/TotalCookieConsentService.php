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
    public function getBannerType(string $defaultBanner, bool $gdpr, string $visitorsCountry, string $visitorsRegion, array $explicitCountries, array $impliedCountries) : string
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
        
        foreach ($explicitCountries as $country)
        {
            if ($country == $visitorsCountry)
            {
                return 'explicit';
            }
        }

        foreach ($impliedCountries as $country)
        {
            if ($country == $visitorsCountry)
            {
                return 'implied';
            }
        }

        return $defaultBanner;
    }

    public function locateVisitor(string $apiKey) : array
    {
        $devMode = Craft::$app->getConfig()->general->devMode;
        $localIps = ['127.0.0.1', '::1'];
        $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
        if (in_array($ip, $localIps) || $devMode)
        {
            return [];
        }

        $cachedData = Craft::$app->getCache()->get('tcc.geo.' . $ip);
        if ($cachedData) {
            $visitorInfo = json_decode($cachedData);
            return [
                'country' => $visitorInfo['country_code'],
                'region' => $visitorInfo['region_code'],
            ];
        }

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', 'http://api.ipapi.com/' . $ip . '?access_key=' . $apiKey);
            $data = json_decode($response->getBody(), true);
            $visitorInfo = [
                'country' => $data['country_code'],
                'region' => $data['region_code'],
            ]; 
            Craft::$app->getCache()->add('tcc.geo.' . $ip, json_encode($visitorInfo), 86400);
            return $visitorInfo;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function renderBanner()
    {
        $settings = TotalCookieConsent::getInstance()->settings;

        $bannerType = $settings->defaultConsentType;

        if (!empty($settings->ipapiKey))
        {
            $visitorInfo = $this->locateVisitor($settings->ipapiKey);
            if (!empty($visitorInfo))
            {
                $bannerType = $this->getBannerType($settings->defaultConsentType, $visitorInfo['country'], $visitorInfo['region'], $settings->explicitConsentTable, $settings->impledConsentTable);
            }
        }

        $template = null;
        $view = Craft::$app->getView();
        $oldMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        switch ($bannerType)
        {
            case 'implied':
                $view->registerAssetBundle('page8\\totalcookieconsent\\assetbundles\\totalcookieconsent\\TotalCookieConsentAsset');
                $template = $view->renderTemplate('total-cookie-consent/banners/implied', [
                    'heading' => $settings->impliedHeading,
                    'copy' => $settings->impliedCopy,
                ]);
                break;
            case 'explicit':
                $view->registerAssetBundle('page8\\totalcookieconsent\\assetbundles\\totalcookieconsent\\TotalCookieConsentAsset');
                $template = $view->renderTemplate('total-cookie-consent/banners/explicit', [
                    'heading' => $settings->explicitHeading,
                    'copy' => $settings->explicitCopy,
                ]);
                break;
            default:
                break;
        }
        $view->setTemplateMode($oldMode);

        return $template;
    }
}
