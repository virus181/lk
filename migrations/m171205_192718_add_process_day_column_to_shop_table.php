<?php

use yii\db\Migration;

/**
 * Handles adding process_day to table `shop`.
 */
class m171205_192718_add_process_day_column_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%shop}}', 'process_day', $this->integer()->after('fulfillment'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%shop}}', 'process_day');
    }
}
