<?php

use yii\db\Migration;

/**
 * Handles adding payment_method to table `{{%order}}`.
 */
class m170628_191152_add_payment_method_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'payment_method', $this->char(255)->after('comment'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
