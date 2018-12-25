<?php

use yii\db\Migration;

/**
 * Class m180926_152911_add_shop_invoice_table
 */
class m180926_152911_add_shop_invoice_table extends Migration
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
        $this->createTable('shop_invoice', [
            'id' => $this->primaryKey(),
            'registry_id' => $this->integer(),
            'type' => $this->integer(),
            'shop_id' => $this->integer(),
            'number' => $this->string(255),
            'status' => $this->boolean()->defaultValue(false),
            'sum' => $this->float()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ], $tableOptions);

        $this->addForeignKey('fk_shop_invoice_registry_id', '{{%shop_invoice}}', 'registry_id', '{{%registry}}', 'id');
        $this->addForeignKey('fk_shop_invoice_shop_id', '{{%shop_invoice}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_shop_invoice_registry_id', '{{%shop_invoice}}');
        $this->dropForeignKey('fk_shop_invoice_shop_id', '{{%shop_invoice}}');
        $this->dropTable('shop_invoice');
    }
}
