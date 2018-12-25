<?php

use yii\db\Migration;

/**
 * Handles adding pay_weight to table `order_delivery`.
 */
class m171008_122634_add_pay_weight_column_to_order_delivery_table extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn('{{%order_delivery}}', 'pay_weight', $this->float()->after('real_weight'));
    }

    public function down()
    {
        $this->dropColumn('{{%order_delivery}}', 'pay_weight');
    }
}
