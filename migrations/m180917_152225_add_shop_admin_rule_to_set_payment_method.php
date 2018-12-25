<?php

use yii\db\Migration;

/**
 * Class m180917_152225_add_shop_admin_rule_to_set_payment_method
 */
class m180917_152225_add_shop_admin_rule_to_set_payment_method extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute("INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`)
VALUES
	('/api/order/set-payment-method',2,NULL,NULL,NULL,1492451410,1492451410);");
        $this->execute("INSERT INTO `auth_item_child` (`parent`, `child`)
VALUES
	('shopAdmin','/api/order/set-payment-method');");
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180917_152225_add_shop_admin_rule_to_set_payment_method cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180917_152225_add_shop_admin_rule_to_set_payment_method cannot be reverted.\n";

        return false;
    }
    */
}
