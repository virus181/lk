<?php

use yii\db\Migration;

class m170320_195554_alter_delivery_number_column_in_order_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%order}}', 'delivery_number', $this->char(255));
    }

    public function down()
    {
        $this->alterColumn('{{%order}}', 'delivery_number', $this->char(255)->notNull());
    }
}
