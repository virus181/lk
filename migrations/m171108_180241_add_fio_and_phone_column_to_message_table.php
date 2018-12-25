<?php

use yii\db\Migration;

/**
 * Handles adding fio_and_phone to table `message`.
 */
class m171108_180241_add_fio_and_phone_column_to_message_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%message}}', 'fio', $this->string(256)->after('title'));
        $this->addColumn('{{%message}}', 'phone', $this->string(32)->after('fio'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%message}}', 'fio');
        $this->dropColumn('{{%message}}', 'phone');
    }
}
