<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product}}`.
 */
class m170220_113302_create_product_table extends Migration
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
        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(1024)->notNull(),
            'code' => $this->string(255)->notNull(),
            'price' => $this->double(2)->notNull(),
            'weight' => $this->double(2)->notNull(),
            'account_id' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey('fk_product_account_id', '{{%product}}', 'account_id', '{{%account}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_product_account_id', '{{%product}}');
        $this->dropTable('{{%product}}');
    }
}
