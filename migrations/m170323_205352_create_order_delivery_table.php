<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_delivery}}`.
 */
class m170323_205352_create_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%order_delivery}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(),
            'type' => $this->char(128),
            'pickupType' => $this->char(128),
            'carrierKey' => $this->char(128)->notNull(),
            'name' => $this->char(128),
            'minTerm' => $this->integer(),
            'maxTerm' => $this->integer(),
            'classNameProvider' => $this->char(255),
            'poindAddress' => $this->text(),
            'cost' => $this->float(),
            'phone' => $this->char(255),
        ]);

        $this->addForeignKey('fk_order_delivery_order_id', '{{%order_delivery}}', 'order_id', '{{%order}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_order_delivery_order_id', '{{%order_delivery}}');
        $this->dropTable('{{%order_delivery}}');
    }
}
