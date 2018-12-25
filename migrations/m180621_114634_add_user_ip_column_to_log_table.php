<?php

use yii\db\Migration;

/**
 * Handles adding user_ip to table `log`.
 */
class m180621_114634_add_user_ip_column_to_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%log}}',
            'user_ip',
            $this->text()->after('data')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%log}}', 'user_ip');
    }
}
