<?php

use yii\db\Migration;

/**
 * Class m170923_123512_add_status_date_column_to_delivery_status
 */
class m170923_123512_add_status_date_column_to_delivery_status extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn('{{%delivery_status}}', 'status_date', $this->integer()->after('status'));
    }

    public function down()
    {
        $this->dropColumn('{{%delivery_status}}', 'status_date');
    }
}
