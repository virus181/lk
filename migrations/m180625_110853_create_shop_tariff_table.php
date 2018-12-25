<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_tariff`.
 */
class m180625_110853_create_shop_tariff_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('shop_tariff', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer(),
            'code' => $this->string(32),
            'work_time' => $this->text(),
//            'first_queue' => $this->string(32),
//            'second_queue' => $this->string(32),
//            'third_queue' => $this->string(32),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('fk_shop_tariff_shop_id', '{{%shop_tariff}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_shop_tariff_shop_id', '{{%shop_phone}}');
        $this->dropTable('shop_tariff');
    }
}
