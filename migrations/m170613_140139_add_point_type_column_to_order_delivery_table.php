<?php

use yii\db\Migration;

/**
 * Handles adding point_type to table `{{%order_delivery}}`.
 */
class m170613_140139_add_point_type_column_to_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order_delivery}}', 'point_type', $this->char(128));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
