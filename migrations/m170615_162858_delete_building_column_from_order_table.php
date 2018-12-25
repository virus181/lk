<?php

use yii\db\Migration;

class m170615_162858_delete_building_column_from_order_table extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%order}}', 'building');
    }

    public function safeDown()
    {
        echo "m170615_162858_delete_building_column_from_order_table cannot be reverted.\n";

        return false;
    }
}
