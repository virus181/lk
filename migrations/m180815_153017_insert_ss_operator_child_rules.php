<?php

use yii\db\Migration;

/**
 * Class m180815_153017_insert_ss_operator_child_rules
 */
class m180815_153017_insert_ss_operator_child_rules extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute(file_get_contents(__DIR__ . '/ssOperatorChildRules.sql'));
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180815_153017_insert_ss_operator_child_rules cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_153017_insert_ss_operator_child_rules cannot be reverted.\n";

        return false;
    }
    */
}
