<?php

use yii\db\Migration;

class m170628_175437_add_columns_to_order_product_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_product}}', 'price', $this->double(2));
        $this->addColumn('{{%order_product}}', 'weight', $this->integer());
    }

    public function safeDown()
    {
        echo "m170628_175437_add_columns_to_order_product_table cannot be reverted.\n";

        return false;
    }
}
