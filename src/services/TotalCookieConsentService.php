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
use page8\totalcookieconsent\records\UserConsent;

/**
 * @author    Pageworks
 * @package   TotalCookieConsent
 * @since     1.0.0
 */
class TotalCookieConsentService extends Component
{
    public $localIps = ['127.0.0.1', '::1'];
    public $testMode = false;
    public $testIp = null;
    // Public Methods
    // =========================================================================

    public function lookupVisitorInfo()
    {
        if($this->testMode == false){
            $devMode = Craft::$app->getConfig()->general->devMode;
            $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
            if (in_array($ip, $this->localIps) || $devMode)
            {
                return [];
            }
        }else{
            $ip = $this->testIp;
        }
        
        $ret = [];
//         $cachedData = Craft::$app->getCache()->get('tcc.' . $ip);
//         if ($cachedData) {
//             $ret = json_decode($cachedData, true);
//         }
        $visitor = UserConsent::find()->where(['ip'=> $ip])->one();
        if($visitor){
            $ret = $visitor;
        }
        return $ret;
    }

    public function acceptImpliedCookies()
    {
        if($this->testMode == false){
            $devMode = Craft::$app->getConfig()->general->devMode;
            $this->localIps = ['127.0.0.1', '::1'];
            $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
            if (in_array($ip, $this->localIps) || $devMode)
            {
                return;
            }
        }else{
            $ip = $this->testIp;
        }
        //$cachedData = Craft::$app->getCache()->get('tcc.' . $ip);
        $settings = TotalCookieConsent::getInstance()->settings;
        $visitor = $this->lookupVisitorInfo();
        $consent = [];
        foreach ($settings->consentTypes as $type)
        {
            $consent[$type['handle']] = true;
        }
        $visitor->setAttributes([
            'visitor_consent'=>json_encode($consent),
        ], false);
        $visitor->save();
        return;
    }

    public function getConsentResponse()
    {
        $ret = [];
        $settings = TotalCookieConsent::getInstance()->settings;
        if($this->testMode == false){
            $devMode = Craft::$app->getConfig()->general->devMode;
            $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
            if (in_array($ip, $this->localIps) || $devMode)
            {
                foreach ($settings->consentTypes as $type)
                {
                    $ret[$type['handle']] = true;
                }
                return $ret;
            }
        }
        
        $visitorInfo = $this->lookupVisitorInfo();
        if (isset($visitorInfo['visitor_consent'])) {
            foreach ($visitorInfo['visitor_consent'] as $key => $value)
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
        $settings = TotalCookieConsent::getInstance()->settings;
        $this->testMode = $settings->testMode;
        $this->testIp = $settings->testIp;

        if($this->testMode == false){
            $devMode = Craft::$app->getConfig()->general->devMode;
            if (in_array($ip, $this->localIps) || $devMode)
            {
                return;
            }
        }else{
            $ip = $this->testIp;
        }
        $visitorInfo = $this->lookupVisitorInfo();
        $consent = [];

        foreach ($settings->consentTypes as $type)
        {
            if (isset($params[$type['handle']]))
            {
                $consent[$type['handle']] = $params[$type['handle']];
            }
            else
            {
                $value = 0;
                if ($type['required'])
                {
                    // The user must have unchecked the box using some HTML editing tomfoolery
                    $value = 1;
                }
                $consent[$type['handle']] = $value;
            }
        }

        //Craft::$app->getCache()->set('tcc.' . $ip, json_encode($visitorInfo), 86400);
        $site = Craft::$app->sites->getCurrentSite();
        $siteId = $site->id;
        $userConsent = UserConsent::find()->where(['ip'=> $ip])->one();
        if (!empty($userConsent))
        {
            $userConsent->setAttributes([
                'visitor_consent'=>json_encode($consent),
            ], false);
            $userConsent->save();   
        }
    }

    public function getBannerType(string $defaultBanner, $visitorsCountry, $visitorsRegion, array $countries, array $regions) : string
    {
        $bannerType = $defaultBanner;

        if (is_null($visitorsCountry) || $visitorsRegion){
            return $bannerType;
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

    public function locateVisitor(string $apiKey)
    {
        if($this->testMode == false){
            $devMode = Craft::$app->getConfig()->general->devMode;
            $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
            if (in_array($ip, $this->localIps) || $devMode)
            {
                return [];
            }
        }else{
            $ip = $this->testIp;
        }
        $visitorInfo = $this->lookupVisitorInfo();
        if (!empty($visitorInfo)) {
            return $visitorInfo;
        }

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', 'http://api.ipapi.com/' . $ip . '?access_key=' . $apiKey);
            $data = json_decode($response->getBody(), true);
            if (isset($data["country_code"]) && isset($data['region_code']) && !is_null($data["country_code"]) && !is_null($data["region_code"])){
                $visitorInfo = [
                    'country' => $data['country_code'],
                    'region' => $data['region_code'],
                ];
                //Craft::$app->getCache()->add('tcc.' . $ip, json_encode($visitorInfo), 86400);
                $site = Craft::$app->sites->getCurrentSite();
                $siteId = $site->id;
                $visitor = new UserConsent();
                $visitor->setAttributes([
                    'siteId'=> Craft::$app->sites->getCurrentSite()->id,
                    'visitor_info'=>json_encode($visitorInfo),
                    'ip'=>$ip,
                ], false);
                $visitor->save();
            } else {
                Craft::warning("Error with API Fetch.");
                $visitor = [
                    'siteId' => Craft::$app->sites->getCurrentSite()->id,
                    'visitor_info' => [
                        'country' => "ERROR",
                        'region' => "ERROR",
                    ],
                    "visitor_consent" => null,
                    'ip' => $ip,
                ];
            }
        } catch (\Exception $e) {
            Craft::warning("Error with API Fetch: " . $e->getMessage() );
            $visitor = [
                'siteId' => Craft::$app->sites->getCurrentSite()->id,
                'visitor_info' => [
                    'country' => "ERROR",
                    'region' => "ERROR",
                ],
                "visitor_consent" => null,
                'ip' => $ip,
            ];
        }
        return $visitor;
    }

    public function renderBanner()
    {
        $settings = TotalCookieConsent::getInstance()->settings;
        $this->testMode = $settings->testMode;
        $this->testIp = $settings->testIp;

        if($this->testMode == false){
            $devMode = Craft::$app->getConfig()->general->devMode;
            $ip = Craft::$app->getRequest()->userIP ?? Craft::$app->getRequest()->remoteIP;
            if (in_array($ip, $this->localIps) || $devMode)
            {
                return;
            }
        }
        $bannerType = $settings->defaultConsentType;
        $visitor = null;
        try{
            if (!empty($settings->ipapiKey))
            {
                $visitor = $this->locateVisitor($settings->ipapiKey);
                if (!empty($visitor))
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
                    $bannerType = $this->getBannerType($settings->defaultConsentType, $visitor->visitor_info['country'], $visitor->visitor_info['region'], $countries, $regions);
                }
            }
            else
            {
                Craft::warning("TCC: No API Key Set");
                $visitor = $this->lookupVisitorInfo();
            }
        } catch (Exception $e) {
            Craft::warning($e->getMessage());
        }

        $template = null;
        $view = Craft::$app->getView();
        $oldMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        if (is_null($visitor) || empty($visitor["visitor_consent"]))
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
                        'url' => $settings->cookiePolicyLink,
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
