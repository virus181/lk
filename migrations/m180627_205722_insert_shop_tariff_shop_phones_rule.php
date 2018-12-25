<?php

use yii\db\Migration;

/**
 * Class m180627_205722_insert_shop_tariff_shop_phones_rule
 */
class m180627_205722_insert_shop_tariff_shop_phones_rule extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute(file_get_contents(__DIR__ . '/insert_rules_auth_item_update_tariff_phone.sql'));
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180627_205722_insert_shop_tariff_shop_phones_rule cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180627_205722_insert_shop_tariff_shop_phones_rule cannot be reverted.\n";

        return false;
    }
    */
}
