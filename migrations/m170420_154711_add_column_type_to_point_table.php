<?php

use yii\db\Migration;

class m170420_154711_add_column_type_to_point_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%points}}', 'type', $this->char(255)->notNull()->after('code'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%points}}', 'type');
    }
}
