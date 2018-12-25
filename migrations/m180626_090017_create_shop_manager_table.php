<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_manager`.
 */
class m180626_090017_create_shop_manager_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('shop_manager', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'shop_id' => $this->integer()
        ]);

        $this->addForeignKey('fk_shop_manager_shop_id', '{{%shop_manager}}', 'shop_id', '{{%shop}}', 'id');
        $this->addForeignKey('fk_shop_manager_user_id', '{{%shop_manager}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_shop_manager_user_id', '{{%shop_manager}}');
        $this->dropForeignKey('fk_shop_manager_shop_id', '{{%shop_manager}}');

        $this->dropTable('shop_manager');
    }
}
