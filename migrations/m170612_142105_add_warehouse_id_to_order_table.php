<?php

use yii\db\Migration;

class m170612_142105_add_warehouse_id_to_order_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'warehouse_id', $this->integer()->after('shop_id'));
        $this->addForeignKey('fk_order_warehouse_id', '{{%order}}', 'warehouse_id', '{{%warehouse}}', 'id');
    }

    public function safeDown()
    {
        echo "m170612_142105_add_warehouse_id_to_order_table cannot be reverted.\n";

        return false;
    }
}
