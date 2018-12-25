<?php

use yii\db\Migration;

/**
 * Class m180926_115421_remove_fias_table
 */
class m180926_115421_remove_fias_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropTable('{{%fias}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('fias', [
            'id' => $this->primaryKey()
        ], $tableOptions);
    }
}
