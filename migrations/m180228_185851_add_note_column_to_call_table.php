<?php

use yii\db\Migration;

/**
 * Handles adding note to table `call`.
 */
class m180228_185851_add_note_column_to_call_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(
            '{{%call}}',
            'note',
            $this->text()->after('tag')
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%call}}', 'note');
    }
}
