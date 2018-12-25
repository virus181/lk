<?php

use yii\db\Migration;

class m170612_142858_add_time_ccolumns_to_warehouse_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%warehouse}}', 'created_at', $this->integer());
        $this->addColumn('{{%warehouse}}', 'updated_at', $this->integer());
    }

    public function safeDown()
    {
        echo "m170612_142858_add_time_ccolumns_to_warehouse_table cannot be reverted.\n";

        return false;
    }
}
