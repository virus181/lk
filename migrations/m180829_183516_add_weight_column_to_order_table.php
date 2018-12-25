<?php

use yii\db\Migration;

/**
 * Handles adding weight to table `order`.
 */
class m180829_183516_add_weight_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%order}}',
            'weight',
            $this->integer()->defaultValue(0)->after('payment_method')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'weight');
    }
}
