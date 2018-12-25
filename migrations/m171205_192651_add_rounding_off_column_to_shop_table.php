<?php

use yii\db\Migration;

/**
 * Handles adding rounding_off to table `shop`.
 */
class m171205_192651_add_rounding_off_column_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%shop}}', 'rounding_off', $this->integer()->after('fulfillment'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%shop}}', 'rounding_off');
    }
}
