<?php

use yii\db\Migration;

/**
 * Handles adding name to table `rate`.
 */
class m180522_131234_add_name_column_to_rate_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%rate}}',
            'name',
            $this->string()->after('id')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rate}}', 'name');
    }
}
