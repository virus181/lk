<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_warehouse`.
 */
class m170924_170905_create_shop_warehouse_table extends Migration
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
        $this->createTable('{{%shop_warehouse}}', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer()->notNull(),
            'warehouse_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk_shop_warehouse_shop_id', '{{%shop_warehouse}}', 'shop_id', '{{%shop}}', 'id');
        $this->addForeignKey('fk_shop_warehouse_warehouse_id', '{{%shop_warehouse}}', 'warehouse_id', '{{%warehouse}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_shop_warehouse_shop_id', '{{%shop_warehouse}}');
        $this->dropForeignKey('fk_shop_warehouse_warehouse_id', '{{%shop_warehouse}}');

        $this->dropTable('{{%shop_warehouse}}');
    }
}
