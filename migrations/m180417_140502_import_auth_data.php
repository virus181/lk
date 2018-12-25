<?php

use yii\db\Migration;

/**
 * Class m180417_140502_import_auth_data
 */
class m180417_140502_import_auth_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute(file_get_contents(__DIR__ . '/fastery_2018-04-17.sql'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180417_140502_import_auth_data cannot be reverted.\n";

        return false;
    }
}
