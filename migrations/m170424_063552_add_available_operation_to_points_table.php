<?php

use yii\db\Migration;

class m170424_063552_add_available_operation_to_points_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%points}}', 'available_operation', $this->integer()->notNull()->after('type'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%points}}', 'available_operation');
    }
}
