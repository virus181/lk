<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_delivery`.
 */
class m171008_160036_create_shop_delivery_table extends Migration
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
        $this->createTable('{{%shop_delivery}}', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer()->notNull(),
            'delivery_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey('fk_shop_delivery_shop_id', '{{%shop_delivery}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_shop_delivery_shop_id', '{{%shop_delivery}}');
        $this->dropForeignKey('fk_shop_delivery_delivery_id', '{{%shop_delivery}}');
        $this->dropTable('{{%shop_delivery}}');
    }
}
