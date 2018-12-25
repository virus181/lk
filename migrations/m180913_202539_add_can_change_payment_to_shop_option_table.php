<?php

use yii\db\Migration;

/**
 * Class m180913_202539_add_can_change_payment_to_shop_option_table
 */
class m180913_202539_add_can_change_payment_to_shop_option_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%shop_option}}',
            'can_change_payment_method',
            $this->boolean()->defaultValue(false)->after('work_scheme_url')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%shop_option}}', 'can_change_payment_method');
    }
}
