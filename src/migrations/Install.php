<?php

namespace krisdrivmailing\mailinglist\migrations;

use Craft;
use craft\db\Migration;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('constant_contact_user_map', [
            'user_id' => 'int(11) NOT NULL',
            'contact_id' => 'VARCHAR(255)'
        ]);

        $this->addForeignKey('fk_user_id_constant_user_map', 'constant_contact_user_map', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_id_constant_user_map', 'constant_contact_user_map');

        $this->dropTable('constant_contact_user_map');
    }
}
