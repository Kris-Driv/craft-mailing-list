<?php

namespace krisdrivmailing\mailinglist\listeners;

use Craft;
use craft\elements\User;
use krisdrivmailing\mailinglist\exceptions\IntegrationException;
use krisdrivmailing\mailinglist\integrations\ElementFieldMap;
use krisdrivmailing\mailinglist\MailingList;
use krisdrivmailing\mailinglist\models\ConstantContactUserMap;
use yii\base\Event;

class UserAccountListener
{

    /**
     * @var ElementFieldMap
     */
    public $fieldMap;

    /**
     * @var bool
     */
    public $creation = false;

    /**
     * @var bool
     */
    public $emailChanged = false;

    public function init()
    {
        $this->fieldMap = new ElementFieldMap([]);

        Event::on(
            User::class,
            User::EVENT_BEFORE_SAVE,
            function($event) {
                $user = User::find()->id($event->sender->getId())->anyStatus()->one();

                if($user->email !== $event->sender->email) {
                    $this->emailChanged = true;
                }

                if($user) {
                    $this->creation = true;
                }
            }
        );

        Event::on(
            User::class,
            User::EVENT_AFTER_SAVE,
            function($event) {
                $user = $event->sender;

                if($this->creation) {
                    $this->onAccountCreation($user);
                    $this->creation = false;
                } else {
                    $this->onAccountUpdate($user);
                }
            }
        );
    }

    public function onAccountUpdate(User $user)
    {
        $this->safeCreateOrUpdateContact($user);
    }

    public function onAccountCreation(User $user)
    {
        $this->safeCreateOrUpdateContact($user);
    }

    public function safeCreateOrUpdateContact(User $user): void
    {
        $data = $this->fieldMap->mapUserFields($user);

        try {
            if($this->emailChanged) {
                $contactMap = ConstantContactUserMap::findOne(['user_id' => $user->getId()]);
            }

            if(isset($contactMap) && $contactMap) {
                $response = MailingList::$plugin->constantContact->updateContact($contactMap->contact_id, $data, "Contact");
            } else {
                $response = MailingList::$plugin->constantContact->createOrUpdateContact($data, MailingList::$plugin->settings->listId);
            }
            
            if(isset($response['contact_id']) && $user->getId()) {
                
                $joinModel = new ConstantContactUserMap();
                $joinModel->user_id = $user->getId();
                $joinModel->contact_id = $response['contact_id'];
                $joinModel->save();

            }
        } catch(IntegrationException $e) {
            throw $e;
            Craft::warning($e->getMessage(), "application");
        }
    }

}