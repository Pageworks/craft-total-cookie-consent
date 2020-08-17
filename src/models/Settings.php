<?php
/**
 * Total Cookie Consent plugin for Craft CMS 3.x
 *
 * The plugin provides total control over the cookie consent collection process and includes three consent collection options: No Consent, Implied Consent, and Explicit Consent. Collection methods can be tailored per country or state to provide an optimal non-intrusive user experience.
 *
 * @link      https://www.page.works/
 * @copyright Copyright (c) 2020 Pageworks
 */

namespace page8\totalcookieconsent\models;

use page8\totalcookieconsent\TotalCookieConsent;

use Craft;
use craft\base\Model;

/**
 * @author    Pageworks
 * @package   TotalCookieConsent
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /** @var string */
    public $ipapiKey = null;

    /** @var string */
    public $impliedCopy;

    /** @var string */
    public $cookiePolicyLink;

    /** @var string */
    public $defaultConsentType = 'none';

    /** @var array */
    public $consentTypes;

    /** @var array */
    public $countriesTable;

    /** @var array */
    public $regionsTable;

    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->consentTypes = [
            [
                'handle' => 'necessary',
                'name' => Craft::t('total-cookie-consent', 'Necessary'),
                'description' => Craft::t('total-cookie-consent', 'These cokies are essential so that you can move around the website and use its features. Without these cookies, some services will not be available.'),
                'defaultOn' => true,
                'required' => true,
                'url' => null,
            ],
            [
                'handle' => 'statistics',
                'name' => Craft::t('total-cookie-consent', 'Statistics'),
                'description' => Craft::t('total-cookie-consent', "These cookies are used to analyze our traffic. We share this information about your use of our site with our analytics partners who may combine it with other information that you've provided to them or that they've collected from your use of their services."),
                'defaultOn' => true,
                'required' => false,
                'url' => null,
            ],
            [
                'handle' => 'marketing',
                'name' => Craft::t('total-cookie-consent', 'Marketing'),
                'description' => Craft::t('total-cookie-consent', "These cookies are used to personalize content, ads, and to provide social media features. We share this information about your use of our site with our social media and advertising partners."),
                'defaultOn' => true,
                'required' => false,
                'url' => null,
            ],
        ];

        $this->impliedCopy = Craft::t('total-cookie-consent', 'We use cookies to improve your experience and deliver prioritized content.');

        $this->countriesTable = [
            [
                'countryCode' => 'AT',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'BE',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'BG',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'HR',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'CY',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'CZ',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'DK',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'EE',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'FI',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'FR',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'DE',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'GR',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'HU',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'IE',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'IT',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'LV',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'LT',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'LU',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'MT',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'NL',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'PL',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'PT',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'RO',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'SK',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'SI',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'ES',
                'bannerType' => 'explicit',
            ],
            [
                'countryCode' => 'SE',
                'bannerType' => 'explicit',
            ],
        ];

        $this->regionsTable = [
            [
                'countryCode' => 'US',
                'regionCode' => 'CA',
                'bannerType' => 'implied',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['impliedCopy'], 'required'],
            [['ipapiKey', 'impliedCopy', 'cookiePolicyLink'], 'string'],
            [['defaultConsentType'], 'in', 'range' => ['none', 'implied', 'explicit']],
        ];
    }
}
