<?php

use yii\db\Migration;

class m170424_064336_add_additional_code_to_points_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%points}}', 'additional_code', $this->char(255)->after('code'));
        $this->dropColumn('{{%points}}', 'additionalCode');
    }

    public function safeDown()
    {
        $this->addColumn('{{%points}}', 'additionalCode', $this->char(255)->after('code'));
        $this->dropColumn('{{%points}}', 'additional_code');
    }
}
