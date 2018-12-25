<?php

use yii\db\Migration;

/**
 * Class m180626_134824_insert_operator_update_rule
 */
class m180626_134824_insert_operator_update_rule extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute(file_get_contents(__DIR__ . '/operatorUpdateRules.sql'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180606_191213_insert_user_list_rule cannot be reverted.\n";
        return false;
    }
}
