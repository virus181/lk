<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%account_user}}`.
 */
class m170220_110807_create_account_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%account_user}}', [
            'account_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk_account_user_account_id_user_id', '{{%account_user}}', ['account_id', 'user_id']);
        $this->addForeignKey('fk_account_user_account_id', '{{%account_user}}', 'account_id', '{{%account}}', 'id');
        $this->addForeignKey('fk_account_user_user_id', '{{%account_user}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_account_user_user_id', '{{%account_user}}');
        $this->dropForeignKey('fk_account_user_account_id', '{{%account_user}}');
        $this->dropPrimaryKey('pk_account_user_account_id_user_id', '{{%account_user}}');

        $this->dropTable('{{%account_user}}');
    }
}
