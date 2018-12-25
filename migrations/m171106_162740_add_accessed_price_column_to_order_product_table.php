<?php

use yii\db\Migration;

/**
 * Handles adding accessed_price to table `order_product`.
 */
class m171106_162740_add_accessed_price_column_to_order_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order_product}}', 'accessed_price', $this->double()->after('price'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order_product}}', 'accessed_price');
    }
}
