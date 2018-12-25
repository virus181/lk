<?php

use yii\db\Migration;

/**
 * Handles the creation of table `order_message`.
 */
class m170815_201355_create_order_message_table extends Migration
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
        $this->createTable('{{%order_message}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'message' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk_order_message_order_id', '{{%order_message}}', 'order_id', '{{%order}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_order_message_order_id', '{{%order_message}}');
        $this->dropTable('{{%order_message}}');
    }
}
