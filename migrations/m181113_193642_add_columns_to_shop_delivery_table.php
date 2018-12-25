<?php

use yii\db\Migration;

/**
 * Class m181113_193642_add_columns_to_shop_delivery_table
 */
class m181113_193642_add_columns_to_shop_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%shop_delivery}}',
            'pickup_type',
            $this->integer()->defaultValue(1)->after('delivery_id')
        );
        $this->addColumn(
            '{{%shop_delivery}}',
            'pickup_time_start',
            $this->string()->defaultValue('10:00:00')->after('pickup_type')
        );
        $this->addColumn(
            '{{%shop_delivery}}',
            'pickup_time_end',
            $this->string()->defaultValue('18:00:00')->after('pickup_time_start')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%shop_delivery}}', 'pickup_time_start');
        $this->dropColumn('{{%shop_delivery}}', 'pickup_time_end');

        return false;
    }
}
