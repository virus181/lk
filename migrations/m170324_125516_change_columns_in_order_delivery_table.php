<?php

use yii\db\Migration;

class m170324_125516_change_columns_in_order_delivery_table extends Migration
{
    public function up()
    {
        $this->renameColumn('{{%order_delivery}}', 'pickupType', 'pickup_type');
        $this->renameColumn('{{%order_delivery}}', 'carrierKey', 'carrier_key');
        $this->renameColumn('{{%order_delivery}}', 'minTerm', 'min_term');
        $this->renameColumn('{{%order_delivery}}', 'maxTerm', 'max_term');
        $this->renameColumn('{{%order_delivery}}', 'classNameProvider', 'class_name_provider');
        $this->renameColumn('{{%order_delivery}}', 'poindAddress', 'point_address');

        $this->addColumn('{{%order_delivery}}', 'pickup_date', $this->integer());
        $this->addColumn('{{%order_delivery}}', 'pickup_address', $this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%order_delivery}}', 'pickup_address');
        $this->dropColumn('{{%order_delivery}}', 'pickup_date');

        $this->renameColumn('{{%order_delivery}}', 'pickup_type', 'pickupType');
        $this->renameColumn('{{%order_delivery}}', 'carrier_key', 'carrierKey');
        $this->renameColumn('{{%order_delivery}}', 'min_term', 'minTerm');
        $this->renameColumn('{{%order_delivery}}', 'max_term', 'maxTerm');
        $this->renameColumn('{{%order_delivery}}', 'class_name_provider', 'classNameProvider');
        $this->renameColumn('{{%order_delivery}}', 'point_address', 'poindAddress');
    }
}
