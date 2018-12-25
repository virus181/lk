<?php

use yii\db\Migration;

/**
 * Class m180829_110932_insert_viehali_sd_to_delivery_table
 */
class m180829_110932_insert_viehali_sd_to_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->execute(file_get_contents(__DIR__ . '/ViehaliSDService.sql'));
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180829_110932_insert_viehali_sd_to_delivery_table cannot be reverted.\n";

        return false;
    }
}
