<?php
/**
 * Total Cookie Consent plugin for Craft CMS 3.x
 *
 * The plugin provides total control over the cookie consent collection process and includes three consent collection options: No Consent, Implied Consent, and Explicit Consent. Collection methods can be tailored per country or state to provide an optimal non-intrusive user experience.
 *
 * @link      https://www.page.works/
 * @copyright Copyright (c) 2020 Pageworks
 */

namespace page8\totalcookieconsent\assetbundles\totalcookieconsent;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Pageworks
 * @package   TotalCookieConsent
 * @since     1.0.0
 */
class ImpliedConsentBannerAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@page8/totalcookieconsent/assetbundles/totalcookieconsent/dist/implied";

        $this->js = [
            'total-cookie-consent-implied.js',
        ];

        $this->css = [
            'total-cookie-consent-implied.css',
        ];

        parent::init();
    }
}
