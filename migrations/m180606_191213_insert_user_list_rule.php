<?php

use yii\db\Migration;

/**
 * Class m180606_191213_insert_user_list_rule
 */
class m180606_191213_insert_user_list_rule extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute(file_get_contents(__DIR__ . '/userListRules.sql'));
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
