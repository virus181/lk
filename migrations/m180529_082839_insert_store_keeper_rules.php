<?php

use yii\db\Migration;

/**
 * Class m180529_082839_insert_store_keeper_rules
 */
class m180529_082839_insert_store_keeper_rules extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute(file_get_contents(__DIR__ . '/storeKeeperRules.sql'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180529_082839_insert_store_keeper_rules cannot be reverted.\n";

        return false;
    }
}
