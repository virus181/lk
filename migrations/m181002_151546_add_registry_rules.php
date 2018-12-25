<?php

use yii\db\Migration;

/**
 * Class m181002_151546_add_registry_rules
 */
class m181002_151546_add_registry_rules extends Migration
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
                                ('/registry/*',2,NULL,NULL,NULL,$time,$time),
                                ('/registry/index',2,NULL,NULL,NULL,$time,$time),
                                ('/registry/view',2,NULL,NULL,NULL,$time,$time),
                                ('/registry/view/own',2,NULL,NULL,NULL,$time,$time);");

        $this->execute("INSERT INTO `auth_item_child` (`parent`, `child`)
                              VALUES
	                            ('shopAdmin','/registry/index'),
	                            ('shopAdmin','/registry/view'),
	                            ('systemAdmin','/registry/*');");
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181002_151546_add_registry_rules cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181002_151546_add_registry_rules cannot be reverted.\n";

        return false;
    }
    */
}
