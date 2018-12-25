<?php

use yii\db\Migration;

/**
 * Handles adding denorm to table `log`.
 */
class m180619_125620_add_denorm_column_to_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%log}}',
            'denorm',
            $this->text()->after('data')
        );
        $this->addColumn(
            '{{%log}}',
            'is_new',
            $this->boolean()->defaultValue(true)->after('data')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%log}}', 'denorm');
        $this->dropColumn('{{%log}}', 'is_new');
    }
}
