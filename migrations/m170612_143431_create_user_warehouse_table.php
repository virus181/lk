<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_warehouse}}`.
 */
class m170612_143431_create_user_warehouse_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%user_warehouse}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'warehouse_id' => $this->integer(),
        ]);

        $this->addForeignKey('fk_user_warehouse_user_id', '{{%user_warehouse}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('fk_user_warehouse_warehouse_id', '{{%user_warehouse}}', 'warehouse_id', '{{%warehouse}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m170612_143431_create_user_warehouse_table cannot be reverted.\n";

        return false;
    }
}
