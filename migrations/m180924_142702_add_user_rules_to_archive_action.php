<?php

use yii\db\Migration;

/**
 * Class m180924_142702_add_user_rules_to_archive_action
 */
class m180924_142702_add_user_rules_to_archive_action extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $time = time();
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute("INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`)
                              VALUES
                                ('/order/is-archive-allowed',2,NULL,NULL,NULL,$time,$time),
                                ('/order/multi-archive',2,NULL,NULL,NULL,$time,$time),
                                ('/order/multi-archive/own',2,NULL,NULL,NULL,$time,$time),
                                ('/order/multi-un-archive/own',2,NULL,NULL,NULL,$time,$time),
                                ('/order/multi-un-archive',2,NULL,NULL,NULL,$time,$time);");

        $this->execute("INSERT INTO `auth_item_child` (`parent`, `child`)
                              VALUES
	                            ('shopAdmin','/order/is-archive-allowed'),
	                            ('shopAdmin','/order/multi-archive/own'),
	                            ('shopAdmin','/order/multi-un-archive/own'),
	                            ('systemAdmin','/order/is-archive-allowed'),
	                            ('systemAdmin','/order/multi-un-archive'),
	                            ('systemAdmin','/order/multi-archive');");
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
}
