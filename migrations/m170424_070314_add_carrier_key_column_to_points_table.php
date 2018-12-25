<?php

use yii\db\Migration;

/**
 * Handles adding carrier_key to table `{{%points}}`.
 */
class m170424_070314_add_carrier_key_column_to_points_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%points}}', 'carrier_key', $this->char(255)->notNull()->after('code'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%points}}', 'carrier_key');
    }
}
