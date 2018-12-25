<?php

use yii\db\Migration;

/**
 * Handles adding partial to table `order_delivery`.
 */
class m180604_200350_add_partial_column_to_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%order_delivery}}',
            'partial',
            $this->boolean()->after('cl')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_delivery}}', 'partial');
    }
}
