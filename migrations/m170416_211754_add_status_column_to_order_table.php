<?php

use yii\db\Migration;

/**
 * Handles adding status to table `{{%order}}`.
 */
class m170416_211754_add_status_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'status', $this->char(128)->after('shop_id'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order}}', 'status');
    }
}
