<?php

use yii\db\Migration;

class m170412_115340_add_access_token_field_to_user_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'access_token', $this->char(32)->after('id'));
        $this->createIndex('idx_user_access_token', '{{%user}}', 'access_token', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_user_access_token', '{{%user}}');
        $this->dropColumn('{{%user}}', 'access_token');
    }
}
