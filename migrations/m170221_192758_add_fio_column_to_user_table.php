<?php

use yii\db\Migration;

/**
 * Handles adding fio to table `{{%user}}`.
 */
class m170221_192758_add_fio_column_to_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%user}}', 'fio', $this->string('512')->after('email'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%user}}', 'fio');
    }
}
