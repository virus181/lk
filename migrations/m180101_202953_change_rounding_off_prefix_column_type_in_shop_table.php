<?php

use yii\db\Migration;

/**
 * Class m180101_202953_change_rounding_off_prefix_column_type_in_shop_table
 */
class m180101_202953_change_rounding_off_prefix_column_type_in_shop_table extends Migration
{
    public function up(){
        $this->alterColumn('{{%shop}}', 'rounding_off_prefix', $this->integer());//timestamp new_data_type
    }

    public function down() {
        $this->alterColumn('{{%shop}}','rounding_off_prefix', $this->char());//int is old_data_type
    }
}
