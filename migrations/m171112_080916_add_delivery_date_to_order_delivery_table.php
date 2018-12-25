<?php

use yii\db\Migration;

/**
 * Class m171112_080916_add_delivery_date_to_order_delivery_table
 */
class m171112_080916_add_delivery_date_to_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order_delivery}}', 'delivery_date', $this->integer()->after('pickup_date'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order_delivery}}', 'delivery_date');
    }
}
