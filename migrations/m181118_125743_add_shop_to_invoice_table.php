<?php

use yii\db\Migration;

/**
 * Class m181118_125743_add_shop_to_invoice_table
 */
class m181118_125743_add_shop_to_invoice_table extends Migration
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
            'shop_id' => $this->integer(),
            'invoice_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('shop_invoice');
    }
}
