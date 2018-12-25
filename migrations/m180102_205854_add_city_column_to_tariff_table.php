<?php

use yii\db\Migration;

/**
 * Handles adding city to table `tariff`.
 */
class m180102_205854_add_city_column_to_tariff_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(
            '{{%tariff}}',
            'city',
            $this->string(255)->after('guid')
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%tariff}}', 'city');
    }
}
