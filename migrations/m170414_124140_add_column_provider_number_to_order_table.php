<?php

use yii\db\Migration;

class m170414_124140_add_column_provider_number_to_order_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'provider_number', $this->char(255)->after('shop_id'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'provider_number');
    }
}
