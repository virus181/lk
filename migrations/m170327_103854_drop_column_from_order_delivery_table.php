<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%column_from_order_delivery}}`.
 */
class m170327_103854_drop_column_from_order_delivery_table extends Migration
{
    public function up()
    {
        $this->dropColumn('{{order_delivery}}', 'pickup_types');

    }

    public function down()
    {
        $this->addColumn('{{order_delivery}}', 'pickup_types', $this->string()->notNull()->after('pickup_type'));
    }
}
