<?php

use yii\db\Migration;

/**
 * Handles adding data to table `log`.
 */
class m180614_200818_add_data_column_to_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%log}}',
            'data',
            $this->text()->after('value')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%log}}', 'data');
    }
}
