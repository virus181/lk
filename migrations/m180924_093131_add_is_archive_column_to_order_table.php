<?php

use yii\db\Migration;

/**
 * Handles adding is_archive to table `order`.
 */
class m180924_093131_add_is_archive_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%order}}',
            'is_archive',
            $this->boolean()->defaultValue(false)->after('is_api')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'is_archive');
    }
}
