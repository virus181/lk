<?php

use yii\db\Migration;

/**
 * Class m180127_163108_add_gabarite_columns_to_product_column
 */
class m180127_163108_add_gabarite_columns_to_product_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%product}}','width', $this->integer()->after('weight'));
        $this->addColumn('{{%product}}','length', $this->integer()->after('width'));
        $this->addColumn('{{%product}}','height', $this->integer()->after('length'));
        $this->addColumn('{{%product}}','is_not_reversible', $this->integer()->after('height'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%product}}', 'width');
        $this->dropColumn('{{%product}}', 'length');
        $this->dropColumn('{{%product}}', 'height');
        $this->dropColumn('{{%product}}', 'is_not_reversible');
    }
}
