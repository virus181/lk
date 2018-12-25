<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%warehouse}}`.
 */
class m170611_153056_create_warehouse_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropTable('{{%warehouse}}');
        $this->createTable('{{%warehouse}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'contact_fio' => $this->string(),
            'contact_phone' => $this->string(),
            'address_id' => $this->integer(),
        ]);
        $this->addForeignKey('fk_warehouse_address_id', '{{%warehouse}}', 'address_id', '{{%address}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_warehouse_address_id', '{{%warehouse}}');
        $this->dropTable('{{%warehouse}}');
    }
}
