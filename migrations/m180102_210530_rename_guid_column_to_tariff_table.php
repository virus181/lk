<?php

use yii\db\Migration;

/**
 * Class m180102_210530_rename_guid_column_to_tariff_table
 */
class m180102_210530_rename_guid_column_to_tariff_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->renameColumn(
            '{{%tariff}}',
            'guid',
            'city_fias_id'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->renameColumn(
            '{{%tariff}}',
            'city_fias_id',
            'guid'
        );
    }
}
