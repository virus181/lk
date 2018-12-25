<?php

use yii\db\Migration;

/**
 * Handles adding label_url to table `{{%order}}`.
 */
class m170612_190322_add_label_url_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'label_url', $this->text()->after('warehouse_id'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m170612_190322_add_label_url_column_to_order_table cannot be reverted.\n";

        return false;
    }
}
