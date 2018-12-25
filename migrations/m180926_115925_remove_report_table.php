<?php

use yii\db\Migration;

/**
 * Class m180926_115925_remove_report_table
 */
class m180926_115925_remove_report_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropTable('{{%report}}');
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
        $this->createTable('{{%report}}', [
            'id' => $this->primaryKey(),
            'dispatch_number' => $this->string(256),
            'carrier_key' => $this->string(16),
            'name' => $this->text(),
            'type' => $this->string(255),
            'report_id' => $this->integer(),
            'sum' => $this->float(),
            'text' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180926_115925_remove_report_table cannot be reverted.\n";

        return false;
    }
    */
}
