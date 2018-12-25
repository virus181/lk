<?php

use yii\db\Migration;

/**
 * Handles adding work_scheme_tariff_id to table `shop`.
 */
class m180625_111336_add_work_scheme_tariff_id_columns_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%shop}}',
            'tariff_id',
            $this->integer()->defaultValue(0)->after('additional_id')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%shop}}', 'tariff_id');
    }
}
