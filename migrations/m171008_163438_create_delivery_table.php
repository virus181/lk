<?php

use yii\db\Migration;

/**
 * Handles the creation of table `delivery`.
 */
class m171008_163438_create_delivery_table extends Migration
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
        $this->createTable('{{%delivery}}', [
            'id' => $this->primaryKey(),
            'carrier_key' => $this->string(255)->notNull(),
            'logo' => $this->string(255),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'status' => $this->boolean()->defaultValue(false),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%delivery}}');
    }
}
