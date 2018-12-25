<?php

use yii\db\Migration;

/**
 * Handles adding as_vip to table `shop_tariff`.
 */
class m180703_154513_add_as_vip_column_to_shop_tariff_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%shop_tariff}}',
            'as_vip',
            $this->boolean()->defaultValue(0)->after('shop_id')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%shop_tariff}}', 'as_vip');
    }
}
