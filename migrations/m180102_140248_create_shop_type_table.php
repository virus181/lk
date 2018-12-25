<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_type`.
 */
class m180102_140248_create_shop_type_table extends Migration
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
        $this->createTable('{{%shop_type}}', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer()->notNull(),
            'type' => $this->string(255)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey('fk_shop_type_shop_id', '{{%shop_type}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_shop_type_shop_id', '{{%shop_type}}');
        $this->dropTable('{{%shop_type}}');
    }
}
