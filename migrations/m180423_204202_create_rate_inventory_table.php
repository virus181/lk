<?php

use yii\db\Migration;

/**
 * Handles the creation of table `rate_inventory`.
 */
class m180423_204202_create_rate_inventory_table extends Migration
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
        $this->createTable('rate_inventory', [
            'id' => $this->primaryKey(),
            'rate_id' => $this->integer(),
            'cost' => $this->integer(),
            'weight_from' => $this->float(),
            'weight_to' => $this->float(),
            'price_from' => $this->float(),
            'price_to' => $this->float(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ], $tableOptions);

        $this->addForeignKey('fk_rate_inventory_rate_id', '{{%rate_inventory}}', 'rate_id', '{{%rate}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('rate_inventory');
    }
}
