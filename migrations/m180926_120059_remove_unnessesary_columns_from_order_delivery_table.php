<?php

use yii\db\Migration;

/**
 * Class m180926_120059_remove_unnessesary_columns_from_order_delivery_table
 */
class m180926_120059_remove_unnessesary_columns_from_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%order_delivery}}', 'real_cost');
        $this->dropColumn('{{%order_delivery}}', 'real_weight');
        $this->dropColumn('{{%order_delivery}}', 'pay_weight');
        $this->dropColumn('{{%order_delivery}}', 'volume');
        $this->dropColumn('{{%order_delivery}}', 'cl');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180926_120059_remove_unnessesary_columns_from_order_delivery_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180926_120059_remove_unnessesary_columns_from_order_delivery_table cannot be reverted.\n";

        return false;
    }
    */
}
