<?php

use yii\db\Migration;

class m170222_070156_rename_account_table extends Migration
{
    public function up()
    {
        $this->renameTable('{{%account}}', '{{%shop}}');
    }

    public function down()
    {
        $this->renameTable('{{%shop}}', '{{%account}}');
    }
}
