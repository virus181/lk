<?php

use yii\db\Migration;

/**
 * Handles adding operator_id to table `call`.
 */
class m180816_152147_add_operator_id_column_to_call_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%call}}',
            'user_id',
            $this->integer()->after('operator_id')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%call}}', 'user_id');
    }
}
