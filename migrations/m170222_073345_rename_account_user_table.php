<?php

use yii\db\Migration;

class m170222_073345_rename_account_user_table extends Migration
{
    public function up()
    {
        $this->renameTable('{{%account_user}}', '{{%shop_user}}');
    }

    public function down()
    {
        $this->renameTable('{{%shop_user}}', '{{%account_user}}');
    }
}
