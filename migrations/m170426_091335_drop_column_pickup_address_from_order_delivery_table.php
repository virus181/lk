<?php

use yii\db\Migration;


class m170426_091335_drop_column_pickup_address_from_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%order_delivery}}', 'pickup_address');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
