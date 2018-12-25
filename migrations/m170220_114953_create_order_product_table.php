<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_product}}`.
 */
class m170220_114953_create_order_product_table extends Migration
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
        $this->createTable('{{%order_product}}', [
            'order_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk_order_product_order_id_product_id', '{{%order_product}}', ['order_id', 'product_id']);
        $this->addForeignKey('fk_order_product_order_id', '{{%order_product}}', 'order_id', '{{%order}}', 'id');
        $this->addForeignKey('fk_order_product_product_id', '{{%order_product}}', 'product_id', '{{%product}}', 'id');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%order_product}}');
    }
}
