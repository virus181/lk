<?php

use yii\db\Migration;

/**
 * Class m181115_113200_add_pickup_type_to_courier_table
 */
class m181115_113200_add_pickup_type_to_courier_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%courier}}',
            'pickup_time_start',
            $this->string()->defaultValue('10:00:00')->after('pickup_date')
        );
        $this->addColumn(
            '{{%courier}}',
            'pickup_time_end',
            $this->string()->defaultValue('18:00:00')->after('pickup_time_start')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%courier}}', 'pickup_time_start');
        $this->dropColumn('{{%courier}}', 'pickup_time_end');

        return false;
    }
}
