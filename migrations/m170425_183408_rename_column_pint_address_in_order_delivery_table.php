<?php

use yii\db\Migration;

class m170425_183408_rename_column_pint_address_in_order_delivery_table extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('{{%order_delivery}}', 'point_address', 'point_id');
        $this->alterColumn('{{%order_delivery}}', 'point_id', $this->integer()->after('type'));
        $this->renameColumn('{{%order_delivery}}', 'receive_point_id', 'pickup_point_id');
        $this->alterColumn('{{%order_delivery}}', 'pickup_point_id', $this->integer()->after('pickup_types'));
    }

    public function safeDown()
    {
        return false;
    }
}
