<?php

use yii\db\Migration;

/**
 * Handles adding accessed_price to table `product`.
 */
class m171106_162719_add_accessed_price_column_to_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%product}}', 'accessed_price', $this->double()->after('price'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%product}}', 'accessed_price');
    }
}
