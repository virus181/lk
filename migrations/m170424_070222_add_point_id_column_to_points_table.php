<?php

use yii\db\Migration;

/**
 * Handles adding point_id to table `{{%points}}`.
 */
class m170424_070222_add_point_id_column_to_points_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%points}}', 'point_id', $this->integer()->notNull()->after('id'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%points}}', 'point_id');
    }
}
