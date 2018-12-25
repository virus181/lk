<?php

use yii\db\Migration;

/**
 * Handles the creation of table `delivery_service`.
 */
class m180530_105555_create_delivery_service_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('delivery_service', [
            'id' => $this->primaryKey(),
            'delivery_id' => $this->integer(),
            'name' => $this->string(256),
            'description' => $this->text(),
            'type' => $this->string(256),
            'status' => $this->integer(),
            'service_key' => $this->string(256),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ], $tableOptions);

        $this->addForeignKey('fk_delivery_service_delivery_id', '{{%delivery_service}}', 'delivery_id', '{{%delivery}}', 'id');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('delivery_service');
    }
}
