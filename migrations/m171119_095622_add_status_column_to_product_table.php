<?php

use yii\db\Migration;

/**
 * Handles adding status to table `product`.
 */
class m171119_095622_add_status_column_to_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%product}}', 'status', $this->integer()->after('count'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%product}}', 'status');
    }
}
