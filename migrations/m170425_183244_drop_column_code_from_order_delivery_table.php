<?php

use yii\db\Migration;

class m170425_183244_drop_column_code_from_order_delivery_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%order_delivery}}', 'code');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
