<?php

use yii\db\Migration;

/**
 * Class m181118_181309_add_rules_to_accounts
 */
class m181118_181309_add_rules_to_accounts extends Migration
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
                                ('/account/index',2,NULL,NULL,NULL,$time,$time),
                                ('/account/view',2,NULL,NULL,NULL,$time,$time);");

        $this->execute("INSERT INTO `auth_item_child` (`parent`, `child`)
                              VALUES
	                            ('shopAdmin','/account/index'),
	                            ('shopAdmin','/account/view'),
	                            ('systemAdmin','/account/index'),
	                            ('systemAdmin','/account/view');");
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181118_181309_add_rules_to_accounts cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181118_181309_add_rules_to_accounts cannot be reverted.\n";

        return false;
    }
    */
}
