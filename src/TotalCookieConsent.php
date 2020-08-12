<?php
/**
 * Total Cookie Consent plugin for Craft CMS 3.x
 *
 * The plugin provides total control over the cookie consent collection process and includes three consent collection options: No Consent, Implied Consent, and Explicit Consent. Collection methods can be tailored per country or state to provide an optimal non-intrusive user experience.
 *
 * @link      https://www.page.works/
 * @copyright Copyright (c) 2020 Pageworks
 */

namespace page8\totalcookieconsent;

use page8\totalcookieconsent\services\TotalCookieConsentService as TotalCookieConsentServiceService;
use page8\totalcookieconsent\variables\TotalCookieConsentVariable;
use page8\totalcookieconsent\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class TotalCookieConsent
 *
 * @author    Pageworks
 * @package   TotalCookieConsent
 * @since     1.0.0
 *
 * @property  TotalCookieConsentServiceService $totalCookieConsentService
 */
class TotalCookieConsent extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var TotalCookieConsent
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'total-cookie-consent/default';
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('totalCookieConsent', TotalCookieConsentVariable::class);
            }
        );

        Craft::$app->view->hook('total-cookie-consent', function(array &$context) {
            return TotalCookieConsent::getInstance()->totalCookieConsentService->renderBanner();
        });

        Craft::info(
            Craft::t(
                'total-cookie-consent',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'total-cookie-consent/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
