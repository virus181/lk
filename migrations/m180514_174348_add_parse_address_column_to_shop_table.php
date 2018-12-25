<?php

use yii\db\Migration;

/**
 * Handles adding parse_address to table `shop`.
 */
class m180514_174348_add_parse_address_column_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%shop}}',
            'parse_address',
            $this->boolean()->after('process_day')->defaultValue(false)
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%shop}}', 'parse_address');
    }
}
