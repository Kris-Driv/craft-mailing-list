<?php
namespace krisdrivmailing\mailinglist\controllers;

use krisdrivmailing\mailinglist\exceptions\IntegrationException;
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
        try {
            MailingList::$plugin->constantContact->initiateAuthentication();
        } catch (IntegrationException $e) {
            \Craft::$app->session->setFlash('message', 'IntegrationException (Init auth): ' . $e->getMessage());

            return $this->redirect('/admin/settings/plugins/mailing-list');
        }
    }

    /**
     * @throws \HttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionHandleOAuthRedirect(string $handle = null): Response
    {
        try {
            if (($code = \Craft::$app->request->getParam('code'))) {
                $response = $this->handleAuthorization($code);

                if (null !== $response) {
                    return $response;
                }
            }

            \Craft::$app->session->setFlash('message', 'Error');
        } catch (IntegrationException $e) {
            \Craft::$app->session->setFlash('message', 'IntegrationException (Handling redirect): ' . $e->getMessage());
        }

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
        try {
            $accessToken = MailingList::$plugin->constantContact->fetchAccessToken($code);

            MailingList::$plugin->constantContact->setSetting('accessToken', $accessToken);
            
            \Craft::$app->session->setFlash('message', 'Access token retrieved successfully!');
        } catch (IntegrationException $e) {
            \Craft::$app->session->setFlash('message', 'IntegrationException (Retrieving token): ' . $e->getMessage());
        }

        return $this->redirect('/admin/settings/plugins/mailing-list');
    }

}
