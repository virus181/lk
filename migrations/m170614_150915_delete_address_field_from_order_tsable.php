<?php

use yii\db\Migration;

class m170614_150915_delete_address_field_from_order_tsable extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%order}}', 'address');
        $this->dropColumn('{{%order}}', 'address_object');
    }

    public function safeDown()
    {
        echo "m170614_150915_delete_address_field_from_order_tsable cannot be reverted.\n";

        return false;
    }
}
