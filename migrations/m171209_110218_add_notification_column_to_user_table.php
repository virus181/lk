<?php

use yii\db\Migration;

/**
 * Handles adding notification to table `user`.
 */
class m171209_110218_add_notification_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(
            '{{%user}}',
            'notify',
            $this->boolean()->defaultValue(true)->after('status')
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%user}}', 'notify');
    }
}
