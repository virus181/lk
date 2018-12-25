<?php

use yii\db\Migration;

/**
 * Class m180224_182541_add_return_number_to_order_table
 */
class m180224_182541_add_return_number_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}','return_number', $this->string(255)->after('dispatch_number'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order}}', 'return_number');
    }
}
