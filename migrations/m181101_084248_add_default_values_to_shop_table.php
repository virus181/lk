<?php

use yii\db\Migration;

/**
 * Class m181101_084248_add_default_values_to_shop_table
 */
class m181101_084248_add_default_values_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('shop', 'fulfillment', $this->boolean()->defaultValue(false));
        $this->alterColumn('shop', 'process_day', $this->integer()->defaultValue(0));
        $this->alterColumn('shop', 'rounding_off', $this->integer()->defaultValue(0));
        $this->alterColumn('shop', 'rounding_off_prefix', $this->integer()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181101_084248_add_default_values_to_shop_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181101_084248_add_default_values_to_shop_table cannot be reverted.\n";

        return false;
    }
    */
}
