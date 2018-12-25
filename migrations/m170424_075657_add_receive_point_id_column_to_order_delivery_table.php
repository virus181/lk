<?php

use yii\db\Migration;

/**
 * Handles adding receive_point_id to table `{{%order_delivery}}`.
 */
class m170424_075657_add_receive_point_id_column_to_order_delivery_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_delivery}}', 'receive_point_id', $this->integer()->after('type'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_delivery}}', 'receive_point_id');
    }
}
