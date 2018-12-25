<?php

use yii\db\Migration;

class m170615_001012_add_date_fields_to_order_delivery_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_delivery}}', 'updated_at', $this->integer()->after('point_type'));
        $this->addColumn('{{%order_delivery}}', 'created_at', $this->integer()->after('point_type'));
    }

    public function safeDown()
    {
        echo "m170615_001012_add_date_fields_to_order_delivery_table cannot be reverted.\n";

        return false;
    }
}
