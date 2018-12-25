<?php

use yii\db\Migration;

/**
 * Handles adding delivery_status to table `order`.
 */
class m170723_151435_add_delivery_status_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'delivery_status', $this->string(2048)->after('status'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order}}', 'delivery_status');
    }
}
