<?php

use yii\db\Migration;

/**
 * Handles adding rounding_off_prefix to table `shop`.
 */
class m171205_192746_add_rounding_off_prefix_column_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%shop}}', 'rounding_off_prefix', $this->char(1)->after('rounding_off'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%shop}}', 'rounding_off_prefix');
    }
}
