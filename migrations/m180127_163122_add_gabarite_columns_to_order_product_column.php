<?php

use yii\db\Migration;

/**
 * Class m180127_163122_add_gabarite_columns_to_order_product_column
 */
class m180127_163122_add_gabarite_columns_to_order_product_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order_product}}','width', $this->integer()->after('weight'));
        $this->addColumn('{{%order_product}}','length', $this->integer()->after('width'));
        $this->addColumn('{{%order_product}}','height', $this->integer()->after('length'));
        $this->addColumn('{{%order_product}}','is_not_reversible', $this->integer()->after('height'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order_product}}', 'width');
        $this->dropColumn('{{%order_product}}', 'length');
        $this->dropColumn('{{%order_product}}', 'height');
        $this->dropColumn('{{%order_product}}', 'is_not_reversible');
    }
}
