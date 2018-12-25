<?php

use yii\db\Migration;

/**
 * Handles adding is_api to table `order`.
 */
class m180412_120957_add_is_api_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(
            '{{%order}}',
            'is_api',
            $this->boolean()->after('height')->defaultValue(false)
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order}}', 'is_api');
    }
}
