<?php

use yii\db\Migration;

/**
 * Handles adding status to table `registry`.
 */
class m181118_164028_add_status_column_to_registry_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%registry}}',
            'status',
            $this->integer()->defaultValue(10)->after('delivery_id')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%registry}}', 'status');
    }
}
