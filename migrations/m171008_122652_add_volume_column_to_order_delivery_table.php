<?php

use yii\db\Migration;

/**
 * Handles adding volume to table `order_delivery`.
 */
class m171008_122652_add_volume_column_to_order_delivery_table extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn('{{%order_delivery}}', 'volume', $this->float()->after('pay_weight'));
    }

    public function down()
    {
        $this->dropColumn('{{%order_delivery}}', 'volume');
    }
}
