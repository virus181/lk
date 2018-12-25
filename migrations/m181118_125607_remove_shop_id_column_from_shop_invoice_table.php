<?php

use yii\db\Migration;

/**
 * Class m181118_125607_remove_shop_id_column_from_shop_invoice_table
 */
class m181118_125607_remove_shop_id_column_from_shop_invoice_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_shop_invoice_shop_id', '{{%shop_invoice}}');
        $this->dropColumn('{{%shop_invoice}}', 'shop_id');
        $this->renameTable('{{%shop_invoice}}', '{{%invoice}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->renameTable('{{%invoice}}', '{{%shop_invoice}}');
        $this->addColumn(
            '{{%shop_invoice}}',
            'shop_id',
            $this->integer()->after('type')
        );
    }
}
