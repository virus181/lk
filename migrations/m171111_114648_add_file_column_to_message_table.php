<?php

use yii\db\Migration;

/**
 * Handles adding file to table `message`.
 */
class m171111_114648_add_file_column_to_message_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%message}}', 'file', $this->string()->after('type'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%message}}', 'file');
    }
}
