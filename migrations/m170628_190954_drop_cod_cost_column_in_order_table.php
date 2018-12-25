<?php

use yii\db\Migration;

class m170628_190954_drop_cod_cost_column_in_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%order}}', 'cod_cost');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
