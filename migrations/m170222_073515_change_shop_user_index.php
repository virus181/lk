<?php

use yii\db\Migration;

class m170222_073515_change_shop_user_index extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_account_user_user_id', '{{%shop_user}}');
        $this->dropForeignKey('fk_account_user_account_id', '{{%shop_user}}');
        $this->dropPrimaryKey('pk_account_user_account_id_user_id', '{{%shop_user}}');

        $this->addPrimaryKey('pk_shop_user_account_id_user_id', '{{%shop_user}}', ['account_id', 'user_id']);
        $this->addForeignKey('fk_shop_user_account_id', '{{%shop_user}}', 'account_id', '{{%shop}}', 'id');
        $this->addForeignKey('fk_shop_user_user_id', '{{%shop_user}}', 'user_id', '{{%user}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('pk_shop_user_account_id_user_id', '{{%shop_user}}');
        $this->dropForeignKey('fk_shop_user_account_id', '{{%shop_user}}');
        $this->dropForeignKey('fk_shop_user_user_id', '{{%shop_user}}');

        $this->addPrimaryKey('pk_account_user_account_id_user_id', '{{%shop_user}}', ['account_id', 'user_id']);
        $this->addForeignKey('fk_account_user_account_id', '{{%shop_user}}', 'account_id', '{{%shop}}', 'id');
        $this->addForeignKey('fk_account_user_user_id', '{{%shop_user}}', 'user_id', '{{%user}}', 'id');
    }
}
