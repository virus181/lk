<?php

use yii\db\Migration;

/**
 * Handles adding address_id to table `{{%order}}`.
 */
class m170614_144517_add_address_id_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'address_id', $this->integer()->after('warehouse_id'));
        $this->addForeignKey('fk_order_address_id', '{{%order}}', 'address_id', '{{%address}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
