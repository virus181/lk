<?php

use yii\db\Migration;

class m170420_151300_add_column_code_to_order_delivery_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_delivery}}', 'code', $this->char(255)->notNull()->after('order_id'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_delivery}}', 'code');
    }
}
