<?php

use yii\db\Migration;

class m170324_201333_add_pickup_types_column_in_order_delivery_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{order_delivery}}', 'pickup_types', $this->string()->notNull()->after('pickup_type'));
    }

    public function down()
    {
        $this->dropColumn('{{order_delivery}}', 'pickup_types');
    }
}
