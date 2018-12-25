<?php

use yii\db\Migration;

/**
 * Class m180506_110628_add_carge_column_to_order_delivery_rable
 */
class m180506_110628_add_carge_column_to_order_delivery_rable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%order_delivery}}',
            'charge',
            $this->float()->after('original_cost')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_delivery}}', 'charge');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180506_110628_add_carge_column_to_order_delivery_rable cannot be reverted.\n";

        return false;
    }
    */
}
