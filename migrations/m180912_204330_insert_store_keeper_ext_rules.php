<?php

use yii\db\Migration;

/**
 * Class m180912_204330_insert_store_keeper_ext_rules
 */
class m180912_204330_insert_store_keeper_ext_rules extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute(file_get_contents(__DIR__ . '/storeKeeperExtRules.sql'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180912_204330_insert_store_keeper_ext_rules cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180912_204330_insert_store_keeper_ext_rules cannot be reverted.\n";

        return false;
    }
    */
}
