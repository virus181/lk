<?php

use yii\db\Migration;

/**
 * Class m180530_150118_update_house_column_in_address_table
 */
class m180530_150118_update_house_column_in_address_table extends Migration
{
    public function up(){
        $this->alterColumn('{{%address}}', 'house', $this->string(16));//timestamp new_data_type
    }

    public function down() {
        $this->alterColumn('{{%address}}','house', $this->string(10));//int is old_data_type
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180530_150118_update_house_column_in_address_table cannot be reverted.\n";

        return false;
    }
    */
}
