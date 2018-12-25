<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_shop}}`.
 */
class m170328_130810_create_user_shop_table extends Migration
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
        $this->createTable('{{%user_shop}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'shop_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk_user_shop_user_id', '{{%user_shop}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('fk_user_shop_shop_id', '{{%user_shop}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_user_shop_shop_id', '{{%user_shop}}');
        $this->dropForeignKey('fk_user_shop_user_id', '{{%user_shop}}');

        $this->dropTable('{{%user_shop}}');
    }
}
