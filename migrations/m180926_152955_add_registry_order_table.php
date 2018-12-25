<?php

use yii\db\Migration;

/**
 * Class m180926_152955_add_registry_order_table
 */
class m180926_152955_add_registry_order_table extends Migration
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
        $this->createTable('registry_order', [
            'id'                    => $this->primaryKey(),
            'registry_id'           => $this->integer(),
            'order_id'              => $this->integer(),
            'invoice_id'            => $this->integer(),
            'total'                 => $this->float()->defaultValue(0),
            'product_cost'          => $this->float()->defaultValue(0),
            'agency_charge'         => $this->float()->defaultValue(0),
            'agency_charge_fastery' => $this->float()->defaultValue(0),
            'delivery_cost'         => $this->float()->defaultValue(0),
            'fastery_charge'        => $this->float()->defaultValue(0),
            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer()
        ], $tableOptions);

        $this->addForeignKey('fk_registry_order_registry_id', '{{%registry_order}}', 'registry_id', '{{%registry}}', 'id');
        $this->addForeignKey('fk_registry_order_order_id', '{{%registry_order}}', 'order_id', '{{%order}}', 'id');
        $this->addForeignKey('fk_registry_order_invoice_id', '{{%registry_order}}', 'invoice_id', '{{%shop_invoice}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_registry_order_registry_id', '{{%registry_order}}');
        $this->dropForeignKey('fk_registry_order_order_id', '{{%registry_order}}');
        $this->dropForeignKey('fk_registry_order_invoice_id', '{{%registry_order}}');
        $this->dropTable('registry_order');
    }
}
