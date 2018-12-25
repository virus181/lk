<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%courier}}`.
 */
class m170616_143148_create_courier_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'courier_id', $this->integer()->after('warehouse_id'));
        $this->createTable('{{%courier}}', [
            'id' => $this->primaryKey(),
            'number' => $this->integer(),
            'registry_label_url' => $this->text(),
            'carrier_key' => $this->char(128),
            'warehouse_id' => $this->integer(),
            'pickup_date' => $this->integer(),
            'courier_call' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        $this->addForeignKey('fk_registry_warehouse_id', '{{%courier}}', 'warehouse_id', '{{%warehouse}}', 'id');
        $this->addForeignKey('fk_order_registry_id', '{{%order}}', 'courier_id', '{{%courier}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
