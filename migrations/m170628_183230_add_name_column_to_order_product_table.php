<?php

use yii\db\Migration;

/**
 * Handles adding name to table `{{%order_product}}`.
 */
class m170628_183230_add_name_column_to_order_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order_product}}', 'name', $this->string(1024));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
