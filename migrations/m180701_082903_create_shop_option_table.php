<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_option`.
 */
class m180701_082903_create_shop_option_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('shop_option', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer(),
            'first_queue' => $this->string(32),
            'second_queue' => $this->string(32),
            'third_queue' => $this->string(32),
            'work_scheme_url' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('fk_shop_option_shop_id', '{{%shop_tariff}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_shop_option_shop_id', '{{%shop_phone}}');
        $this->dropTable('shop_option');
    }
}
