<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tariff`.
 */
class m171205_193326_create_tariff_table extends Migration
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
        $this->createTable('{{%tariff}}', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer(),
            'type' => $this->string(16),
            'carrier_key' => $this->string(16),
            'additional_sum' => $this->integer(),
            'additional_sum_type' => $this->string(1),
            'additional_sum_prefix' => $this->string(1),
            'total' => $this->boolean(),
            'guid' => $this->text(),
            'min_price' => $this->integer(),
            'max_price' => $this->integer(),
            'min_weight' => $this->integer(),
            'max_weight' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
        $this->addForeignKey('fk_tariff_shop_id', '{{%tariff}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_tariff_shop_id', '{{%tariff}}');
        $this->dropTable('{{%tariff}}');
    }
}
