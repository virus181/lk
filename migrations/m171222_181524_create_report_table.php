<?php

use yii\db\Migration;

/**
 * Handles the creation of table `report`.
 */
class m171222_181524_create_report_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
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

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%report}}');
    }
}
