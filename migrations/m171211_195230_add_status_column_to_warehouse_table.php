<?php

use yii\db\Migration;

/**
 * Handles adding status to table `warehouse`.
 */
class m171211_195230_add_status_column_to_warehouse_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(
            '{{%warehouse}}',
            'status',
            $this->integer()->after('address_id')
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%warehouse}}', 'status');
    }
}
