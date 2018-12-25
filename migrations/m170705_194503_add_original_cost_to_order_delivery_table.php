<?php

use yii\db\Migration;

class m170705_194503_add_original_cost_to_order_delivery_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_delivery}}', 'original_cost', $this->float()->after('cost'));
    }

    public function safeDown()
    {
        echo "m170705_194503_add_original_cost_to_order_delivery_table cannot be reverted.\n";

        return false;
    }
}
