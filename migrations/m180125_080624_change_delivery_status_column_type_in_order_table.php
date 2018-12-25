<?php

use yii\db\Migration;

/**
 * Class m180125_080624_change_delivery_status_column_type_in_order_table
 */
class m180125_080624_change_delivery_status_column_type_in_order_table extends Migration
{
    public function up(){
        $this->alterColumn('{{%order}}', 'delivery_status', $this->text());//timestamp new_data_type
    }

    public function down() {
        $this->alterColumn('{{%order}}','delivery_status', $this->text());//int is old_data_type
    }
}
