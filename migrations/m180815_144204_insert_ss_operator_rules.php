<?php

use yii\db\Migration;

/**
 * Class m180815_144204_insert_ss_operator_rules
 */
class m180815_144204_insert_ss_operator_rules extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute(file_get_contents(__DIR__ . '/ssOperatorRules.sql'));
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "Cannot be reverted.\n";

        return false;
    }
}
