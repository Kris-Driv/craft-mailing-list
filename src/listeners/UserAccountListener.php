<?php

namespace krisdrivmailing\mailinglist\listeners;

use craft\elements\User;
use krisdrivmailing\mailinglist\integrations\ElementFieldMap;
use krisdrivmailing\mailinglist\MailingList;
use yii\base\Event;

class UserAccountListener
{

    /**
     * @var ElementFieldMap
     */
    public $fieldMap;

    public function init()
    {
        $this->fieldMap = new ElementFieldMap([]);

        Event::on(
            User::class,
            User::EVENT_BEFORE_SAVE,
            function($event) {
                $user = User::findIdentity($event->sender->getId());

                if(!$user) {
                    $this->onAccountCreation($event->sender);
                } else {
                    $this->onAccountUpdate($event->sender);
                }
            }
        );
    }

    public function onAccountUpdate(User $user)
    {
        $data = $this->fieldMap->mapUserFields($user);

        MailingList::$plugin->constantContact->createOrUpdateContact($data, MailingList::$plugin->settings->listId);
    }

    public function onAccountCreation(User $user)
    {
        $data = $this->fieldMap->mapUserFields($user);

        MailingList::$plugin->constantContact->createOrUpdateContact($data, MailingList::$plugin->settings->listId);
    }

}