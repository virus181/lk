<?php

use yii\db\Migration;

/**
 * Class m180501_214111_add_index_to_address_id_to_rate_table
 */
class m180501_214111_add_index_to_address_id_to_rate_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addForeignKey('fk_rate_address_id', '{{%rate}}', 'address_id', '{{%address}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_rate_address_id', '{{%rate}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180501_214111_add_index_to_address_id_to_rate_table cannot be reverted.\n";

        return false;
    }
    */
}
