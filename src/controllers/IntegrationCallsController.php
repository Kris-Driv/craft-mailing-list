<?php
namespace krisdrivmailing\mailinglist\controllers;

use krisdrivmailing\mailinglist\exceptions\IntegrationException;
use krisdrivmailing\mailinglist\MailingList;
use craft\web\Controller;
use yii\web\Response;

class IntegrationCallsController extends Controller
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

    public function actionGetLists()
    {
        $constantContact = MailingList::$plugin->constantContact;

        return json_encode($constantContact->fetchLists(), JSON_PRETTY_PRINT);
    }

    public function actionGetContacts($listId)
    {
        $constantContact = MailingList::$plugin->constantContact;

        return json_encode($constantContact->fetchContacts($listId), JSON_PRETTY_PRINT);
    }

}
