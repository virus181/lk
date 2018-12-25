<?php

use yii\db\Migration;

class m170414_155922_add_pickup_types_to_order_delivery_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_delivery}}', 'pickup_types', $this->string()->after('pickup_type'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_delivery}}', 'pickup_types');
    }
}
