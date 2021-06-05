<?php
namespace krisdrivmailing\mailinglist\controllers;

use krisdrivmailing\mailinglist\exceptions\IntegrationException as ExceptionsIntegrationException;
use krisdrivmailing\mailinglist\MailingList;
use craft\web\Controller;
use yii\web\Response;

class IntegrationAuthController extends Controller
{
    /**
     * Make sure this controller requires a logged in member.
     */
    public function init()
    {
        $this->requireAdmin();

        if (!\Craft::$app->request->getIsConsoleRequest()) {
            $this->requireLogin();
        }

        parent::init();
    }

    public function actionStartOAuthProcess(): Response
    {
        MailingList::$plugin->constantContact->initiateAuthentication();
    }

    /**
     * @throws \HttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionHandleOAuthRedirect(string $handle = null): Response
    {
        if (($code = \Craft::$app->request->getParam('code'))) {
            $response = $this->handleAuthorization($code);

            if (null !== $response) {
                return $response;
            }
        }

        \Craft::$app->session->setFlash('message', 'Error');

        return $this->redirect('/admin/settings/plugins/mailing-list');
    }

     /**
     * Clear previous access and access token
     */
    public function actionClearSession()
    {
        MailingList::$plugin->constantContact->setSetting('refreshToken', null);
        MailingList::$plugin->constantContact->setSetting('accessToken', null);

        \Craft::$app->session->setFlash('message', 'Session has been cleared');

        return $this->redirect('/admin/settings/plugins/mailing-list');
    }

    /**
     * Checks integration connection.
     */
    public function actionCheckIntegrationConnection()
    {
        try {
            return $this->asJson(['success' => MailingList::$plugin->constantContact->checkConnection()]);
        } catch (ExceptionsIntegrationException $exception) {
            return $this->asJson(['success' => false, 'errors' => $exception->getMessage()]);
        }
    }

    /**
     * Handle OAuth2 authorization.
     *
     * @return null|Response
     */
    private function handleAuthorization($code)
    {
        // TODO
        $accessToken = MailingList::$plugin->constantContact->fetchAccessToken($code);

        MailingList::$plugin->constantContact->setSetting('accessToken', $accessToken);
        
        \Craft::$app->session->setFlash('message', 'Access token retrieved successfully!');

        return $this->redirect('/admin/settings/plugins/mailing-list');
    }

}
