<?php

use yii\db\Migration;

/**
 * Class m171011_190928_add_phone_columnt_to_shop_table
 */
class m171011_190928_add_phone_columnt_to_shop_table extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn('{{%shop}}', 'phone', $this->string(32)->after('name'));
    }

    public function down()
    {
        $this->dropColumn('{{%shop}}', 'phone');
    }
}
