<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 */
class m170220_114820_create_order_table extends Migration
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
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'shop_order_number' => $this->char(255),
            'delivery_number' => $this->char(255)->notNull(),
            'delivery_return_number' => $this->char(255),
            'fio' => $this->char(255)->notNull(),
            'email' => $this->char(255),
            'phone' => $this->char(255)->notNull(),
            'country' => $this->string(512)->notNull(),
            'city' => $this->string(512)->notNull(),
            'street' => $this->string(512)->notNull(),
            'house' => $this->char(10)->notNull(),
            'flat' => $this->char(10),
            'housing' => $this->char(10),
            'building' => $this->char(10),
            'postcode' => $this->char(10),
            'address' => $this->string(1024)->notNull(),
            'comment' => $this->string(512),
            'address_object' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%order}}');
    }
}
