<?php

namespace krisdrivmailing\mailinglist\listeners;

use craft\elements\User;
use yii\base\Event;

class UserAccountListener extends AbstractListener
{

    public function init()
    {
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
        // Update user email
        var_dump('User updated: ' . $user->getName());
    }

    public function onAccountCreation(User $user)
    {
        // Create new contact
        var_dump('User created: ' . $user->getName());
    }

}