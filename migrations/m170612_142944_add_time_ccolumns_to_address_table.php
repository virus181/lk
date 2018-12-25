<?php

use yii\db\Migration;

class m170612_142944_add_time_ccolumns_to_address_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%address}}', 'created_at', $this->integer());
        $this->addColumn('{{%address}}', 'updated_at', $this->integer());
    }

    public function safeDown()
    {
        echo "m170612_142944_add_time_ccolumns_to_address_table cannot be reverted.\n";

        return false;
    }
}
