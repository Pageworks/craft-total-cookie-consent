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
    public $impliedHeading;

    /** @var string */
    public $impliedCopy;

    /** @var string */
    public $explicitHeading;

    /** @var string */
    public $explicitCopy;

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
                'defaultOn' => true,
                'required' => true,
            ],
            [
                'handle' => 'statistics',
                'name' => Craft::t('total-cookie-consent', 'Statistics'),
                'defaultOn' => true,
                'required' => false,
            ],
            [
                'handle' => 'marketing',
                'name' => Craft::t('total-cookie-consent', 'Marketing'),
                'defaultOn' => true,
                'required' => false,
            ],
        ];

        $this->explicitHeading = Craft::t('total-cookie-consent', 'This website uses cookies');
        $this->explicitCopy = Craft::t('total-cookie-consent', "We use cookies to personalize content and ads, to provide social media features, and to analyze our traffic. We also share information about your use of our site with our social media, advertising and analytics partners who may combine it with other information that you've provided to them or that they've collected from your use of their services.");

        $this->impliedHeading = Craft::t('total-cookie-consent', 'This website uses cookies');
        $this->impliedCopy = Craft::t('total-cookie-consent', "We use cookies to personalize content and ads, to provide social media features, and to analyze our traffic. We also share information about your use of our site with our social media, advertising and analytics partners who may combine it with other information that you've provided to them or that they've collected from your use of their services.");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['impliedHeading', 'impliedCopy', 'explicitHeading', 'explicitCopy', 'consentTypes'], 'required'],
            [['ipapiKey', 'impliedHeading', 'impliedCopy', 'explicitHeading', 'explicitCopy'], 'string'],
            [['ipapiKey'], 'boolean'],
            [['defaultConsentType'], 'in', 'range' => ['none', 'implied', 'explicit']],
        ];
    }
}
