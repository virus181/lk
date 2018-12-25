<?php

use yii\db\Migration;

/**
 * Handles the creation of table `call`.
 */
class m180220_181356_create_call_table extends Migration
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
        $this->createTable('{{%call}}', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer()->notNull(),
            'order_id' => $this->integer(),
            'key' => $this->string(255),
            'direction' => $this->string(255),
            'operator_id' => $this->integer(),
            'operator_name' => $this->string(255),
            'call_id' => $this->integer(),
            'answer_time' => $this->string(255),
            'ring_time' => $this->string(255),
            'end_time' => $this->string(255),
            'close_time' => $this->string(255),
            'rec_uid' => $this->string(255),
            'uid' => $this->string(255),
            'shop_phone' => $this->string(32),
            'client_phone' => $this->string(32),
            'tag' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey('fk_call_shop_id', '{{%call}}', 'shop_id', '{{%shop}}', 'id');
        $this->addForeignKey('fk_call_order_id', '{{%call}}', 'order_id', '{{%order}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_call_shop_id', '{{%call}}');
        $this->dropForeignKey('fk_call_order_id', '{{%call}}');
        $this->dropTable('{{%call}}');
    }
}
