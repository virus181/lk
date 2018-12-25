<?php

use yii\db\Migration;

class m170614_152855_delete_address_fields_from_order_table extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%order}}', 'country');
        $this->dropColumn('{{%order}}', 'city');
        $this->dropColumn('{{%order}}', 'street');
        $this->dropColumn('{{%order}}', 'house');
        $this->dropColumn('{{%order}}', 'flat');
        $this->dropColumn('{{%order}}', 'housing');
        $this->dropColumn('{{%order}}', 'postcode');
    }

    public function safeDown()
    {
        echo "m170614_152855_delete_address_fields_from_order_table cannot be reverted.\n";

        return false;
    }
}
