<?php
/**
 * Total Cookie Consent plugin for Craft CMS 3.x
 *
 * The plugin provides total control over the cookie consent collection process and includes three consent collection options: No Consent, Implied Consent, and Explicit Consent. Collection methods can be tailored per country or state to provide an optimal non-intrusive user experience.
 *
 * @link      https://www.page.works/
 * @copyright Copyright (c) 2020 Pageworks
 */

namespace page8\totalcookieconsent\controllers;

use page8\totalcookieconsent\TotalCookieConsent;

use Craft;
use craft\web\Controller;

/**
 * @author    Pageworks
 * @package   TotalCookieConsent
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['save',];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $ip = $request->userIP ?? $request->remoteIP;
        return TotalCookieConsent::getInstance()->totalCookieConsentService->save($ip, $request->getBodyParams());
    }
}
