<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_phone`.
 */
class m180625_110846_create_shop_phone_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('shop_phone', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer(),
            'phone' => $this->string(32),
            'provider_code' => $this->string(32),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('fk_shop_phone_shop_id', '{{%shop_phone}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_shop_phone_shop_id', '{{%shop_phone}}');
        $this->dropTable('shop_phone');
    }
}
