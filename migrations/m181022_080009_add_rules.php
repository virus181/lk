<?php

use yii\db\Migration;

/**
 * Class m181022_080009_add_rules
 */
class m181022_080009_add_rules extends Migration
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
                                ('/order/set-dimensions',2,NULL,NULL,NULL,$time,$time),
                                ('/order/update-dimensions',2,NULL,NULL,NULL,$time,$time),
                                ('/order/apply-dimension-delivery',2,NULL,NULL,NULL,$time,$time);");

        $this->execute("INSERT INTO `auth_item_child` (`parent`, `child`)
                              VALUES
	                            ('shopAdmin','/order/set-dimensions'),
	                            ('shopAdmin','/order/update-dimensions'),
	                            ('shopAdmin','/order/apply-dimension-delivery'),
	                            ('systemAdmin','/order/set-dimensions'),
	                            ('systemAdmin','/order/update-dimensions'),
	                            ('systemAdmin','/order/apply-dimension-delivery');");
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181022_080009_add_rules cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181022_080009_add_rules cannot be reverted.\n";

        return false;
    }
    */
}
