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
use craft\utilities\ClearCaches;
use craft\events\RegisterCacheOptionsEvent;

use yii\base\Event;
use page8\totalcookieconsent\services\TotalCookieConsentService;

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
        $this->setComponents([
            'totalCookieConsentService' => TotalCookieConsentService::class
        ]);
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('tcc', TotalCookieConsentVariable::class);
            }
        );

        Craft::$app->view->hook('total-cookie-consent', function(array &$context) {
            return TotalCookieConsent::getInstance()->totalCookieConsentService->renderBanner();
        });

        Event::on(ClearCaches::class, ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
            function (RegisterCacheOptionsEvent $event) {
                $event->options[] = [
                    'key' => 'cookie-consent-cache',
                    'label' => "Cookie consent responses",
                    'action' => function() {
                        \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('{{%totalcookieconsent_userconsent}}')
                            ->execute();
                    }
                ];
            }
        );

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
