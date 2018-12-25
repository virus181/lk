<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_event}}`.
 */
class m170417_071539_create_order_event_table extends Migration
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
        $this->createTable('{{%order_event}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'code' => $this->char(255)->notNull(),
            'sender_class_name' => $this->char(255)->notNull(),
            'dispatcher_class_name' => $this->char(255)->notNull(),
            'text' => $this->integer()->notNull(),
            'desc' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey('fk_order_event_order_id', '{{%order_event}}', 'order_id', '{{%order}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_order_event_order_id', '{{%order_event}}');
        $this->dropTable('{{%order_event}}');
    }
}
