<?php

use yii\db\Migration;

/**
 * Handles adding cod_cost to table `{{%order}}`.
 */
class m170620_142803_add_cod_cost_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'cod_cost', $this->double(2)->notNull()->after('address_id'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
