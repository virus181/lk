<?php

use yii\db\Migration;

/**
 * Handles the creation of table `log`.
 */
class m171017_124347_create_log_table extends Migration
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
        $this->createTable('{{%log}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'model_id' => $this->integer(),
            'model' => $this->string(255),
            'attribute' => $this->string(255),
            'old_value' => $this->text(),
            'value' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey('fk_log_user_id', '{{%log}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_log_user_id', '{{%log}}');
        $this->dropTable('{{%log}}');
    }
}
