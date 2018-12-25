<?php

use yii\db\Migration;

/**
 * Handles adding city to table `{{%order_delivery}}`.
 */
class m170424_074239_add_city_column_to_order_delivery_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_delivery}}', 'city', $this->string()->notNull()->after('code'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_delivery}}', 'city');
    }
}
