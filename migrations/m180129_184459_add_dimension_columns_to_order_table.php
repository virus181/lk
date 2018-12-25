<?php

use yii\db\Migration;

/**
 * Handles adding dimension to table `order`.
 */
class m180129_184459_add_dimension_columns_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}','width', $this->integer()->after('payment_method'));
        $this->addColumn('{{%order}}','length', $this->integer()->after('width'));
        $this->addColumn('{{%order}}','height', $this->integer()->after('length'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order}}', 'width');
        $this->dropColumn('{{%order}}', 'length');
        $this->dropColumn('{{%order}}', 'height');
    }
}
