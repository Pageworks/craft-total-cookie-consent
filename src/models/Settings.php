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

    /** @var boolean */
    public $gdprBanner = true;

    /** @var string */
    public $impliedCopy;

    /** @var string */
    public $cookiePolicyLink;

    /** @var string */
    public $defaultConsentType = 'none';

    /** @var array */
    public $consentTypes;

    /** @var array */
    public $impledConsentTable = [];

    /** @var array */
    public $explicitConsentTable = [];

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
            ],
            [
                'handle' => 'statistics',
                'name' => Craft::t('total-cookie-consent', 'Statistics'),
                'description' => Craft::t('total-cookie-consent', "These cookies are used to analyze our traffic. We share this information about your use of our site with our analytics partners who may combine it with other information that you've provided to them or that they've collected from your use of their services."),
                'defaultOn' => true,
                'required' => false,
            ],
            [
                'handle' => 'marketing',
                'name' => Craft::t('total-cookie-consent', 'Marketing'),
                'description' => Craft::t('total-cookie-consent', "These cookies are used to personalize content, ads, and to provide social media features. We share this information about your use of our site with our social media and advertising partners."),
                'defaultOn' => true,
                'required' => false,
            ],
        ];

        $this->impliedCopy = Craft::t('total-cookie-consent', 'We use cookies to improve your experience and deliver prioritized content.');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['impliedCopy'], 'required'],
            [['ipapiKey', 'impliedCopy', 'cookiePolicyLink'], 'string'],
            [['gdprBanner'], 'boolean'],
            [['defaultConsentType'], 'in', 'range' => ['none', 'implied', 'explicit']],
        ];
    }
}
