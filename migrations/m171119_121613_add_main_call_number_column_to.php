<?php

use yii\db\Migration;

/**
 * Class m171119_121613_add_main_call_number_column_to
 */
class m171119_121613_add_main_call_number_column_to extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%courier}}', 'main_courier_id', $this->integer()->after('number'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%courier}}', 'main_courier_id');
    }
}
