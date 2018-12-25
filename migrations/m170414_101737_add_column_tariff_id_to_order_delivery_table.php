<?php

use yii\db\Migration;

class m170414_101737_add_column_tariff_id_to_order_delivery_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_delivery}}', 'tariff_id', $this->integer()->after('carrier_key'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_delivery}}', 'tariff_id');
    }
}
