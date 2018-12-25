<?php

use yii\db\Migration;

/**
 * Handles adding real_weight to table `order_delivery`.
 */
class m170910_171601_add_real_weight_column_to_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order_delivery}}', 'real_weight', $this->float()->after('point_type'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order_delivery}}', 'real_weight');
    }
}
