<?php

use yii\db\Migration;

/**
 * Handles adding class_name_provider to table `{{%courier}}`.
 */
class m170619_105312_add_class_name_provider_column_to_courier_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%courier}}', 'class_name_provider', $this->char(255)->after('courier_call'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
