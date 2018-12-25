<?php

use yii\db\Migration;

/**
 * Handles adding closed to table `order_delivery`.
 */
class m180118_192208_add_closed_column_to_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(
            '{{%order_delivery}}',
            'cl',
            $this->boolean()->after('volume')
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order_delivery}}', 'cl');
    }
}
