<?php

use yii\db\Migration;

class m170612_103253_alter_column_full_address_in_address_table extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%address}}', 'full_address', $this->text());
    }

    public function safeDown()
    {
        echo "m170612_103253_alter_column_full_address_in_address_table cannot be reverted.\n";

        return false;
    }
}
