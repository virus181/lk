<?php

use yii\db\Migration;

/**
 * Class m180926_121830_remove_unnessesary_columns_from_order_table
 */
class m180926_121830_remove_unnessesary_columns_from_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%order}}', 'delivery_number');
        $this->dropColumn('{{%order}}', 'delivery_return_number');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180926_121830_remove_unnessesary_columns_from_order_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180926_121830_remove_unnessesary_columns_from_order_table cannot be reverted.\n";

        return false;
    }
    */
}
