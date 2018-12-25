<?php

use yii\db\Migration;

/**
 * Handles adding real_cost to table `order_delivery`.
 */
class m170910_171626_add_real_cost_column_to_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order_delivery}}', 'real_cost', $this->float()->after('point_type'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order_delivery}}', 'real_cost');
    }
}
