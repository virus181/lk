<?php

use yii\db\Migration;

/**
 * Handles the creation of table `rate`.
 */
class m180218_132012_create_rate_table extends Migration
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
        $this->createTable('rate', [
            'id' => $this->primaryKey(),
            'shop_id' => $this->integer(),
            'fias_to' => $this->string(255),
            'address_id' => $this->integer(),
            'type' => $this->string(32),
            'min_term' => $this->integer(),
            'max_term' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ], $tableOptions);

        $this->addForeignKey('fk_rate_shop_id', '{{%rate}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('rate');
    }
}
