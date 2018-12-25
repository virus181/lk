<?php

use yii\db\Migration;

class m170613_144246_add_default_warehouse_id_to_shop_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%shop}}', 'default_warehouse_id', $this->integer());
    }

    public function safeDown()
    {
        echo "m170613_144246_add_default_warehouse_id_to_shop_table cannot be reverted.\n";

        return false;
    }
}
