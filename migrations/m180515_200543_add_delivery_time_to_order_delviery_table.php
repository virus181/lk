<?php

use yii\db\Migration;

/**
 * Class m180515_200543_add_delivery_time_to_order_delviery_table
 */
class m180515_200543_add_delivery_time_to_order_delviery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%order_delivery}}',
            'time_start',
            $this->time()->after('delivery_date')->defaultValue('10:00')
        );
        $this->addColumn(
            '{{%order_delivery}}',
            'time_end',
            $this->time()->after('time_start')->defaultValue('19:00')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_delivery}}', 'time_start');
        $this->dropColumn('{{%order_delivery}}', 'time_end');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180515_200543_add_delivery_time_to_order_delviery_table cannot be reverted.\n";

        return false;
    }
    */
}
