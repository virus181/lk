<?php

use yii\db\Migration;

/**
 * Handles adding internal_number_group_id_location to table `user`.
 */
class m180625_111436_add_internal_number_group_id_location_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%user}}',
            'internal_number',
            $this->integer()->defaultValue(0)->after('fio')
        );
        $this->addColumn(
            '{{%user}}',
            'location',
            $this->string(16)->after('internal_number')
        );
        $this->addColumn(
            '{{%user}}',
            'group_id',
            $this->integer()->defaultValue(0)->after('internal_number')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'internal_number');
        $this->dropColumn('{{%user}}', 'location');
        $this->dropColumn('{{%user}}', 'group_id');
    }
}
