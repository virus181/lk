<?php

use yii\db\Migration;

/**
 * Class m180102_213034_change_total_column_to_tariff_table
 */
class m180102_213034_change_total_column_to_tariff_table extends Migration
{
    public function up(){
        $this->alterColumn('{{%tariff}}', 'total', $this->float());//timestamp new_data_type
    }

    public function down() {
        $this->alterColumn('{{%tariff}}','total', $this->boolean());//int is old_data_type
    }
}
