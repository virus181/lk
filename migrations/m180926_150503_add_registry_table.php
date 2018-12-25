<?php

use yii\db\Migration;

/**
 * Class m180926_150503_add_registry_table
 */
class m180926_150503_add_registry_table extends Migration
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
        $this->createTable('registry', [
            'id' => $this->primaryKey(),
            'number' => $this->string(255),
            'name' => $this->string(255),
            'delivery_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ], $tableOptions);

        $this->addForeignKey('fk_registry_delivery_id', '{{%registry}}', 'delivery_id', '{{%delivery}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_registry_delivery_id', '{{%registry}}');
        $this->dropTable('registry');
    }
}
