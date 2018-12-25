<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%delivery_status}}`.
 */
class m170220_145434_create_delivery_status_table extends Migration
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
        $this->createTable('{{%delivery_status}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'status' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk_delivery_status_order_id', '{{%delivery_status}}', 'order_id', '{{%order}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_delivery_status_order_id', '{{%delivery_status}}');
        $this->dropTable('{{%delivery_status}}');
    }
}
