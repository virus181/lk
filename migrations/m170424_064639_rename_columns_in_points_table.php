<?php

use yii\db\Migration;

class m170424_064639_rename_columns_in_points_table extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('{{%points}}', 'cityGuid', 'city_guid');
        $this->renameColumn('{{%points}}', 'className', 'class_name');
    }

    public function safeDown()
    {
        $this->renameColumn('{{%points}}', 'city_guid', 'cityGuid');
        $this->renameColumn('{{%points}}', 'class_name', 'className');
    }
}
