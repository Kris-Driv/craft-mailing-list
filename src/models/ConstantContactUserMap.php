<?php

namespace krisdrivmailing\mailinglist\models;

use yii\db\ActiveRecord;

class ConstantContactUserMap extends ActiveRecord
{

    public static function tableName()
    {
        return 'constant_contact_user_map';
    }

}