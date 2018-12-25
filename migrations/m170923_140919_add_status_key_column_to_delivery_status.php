<?php

use yii\db\Migration;

/**
 * Class m170923_140919_add_status_key_column_to_delivery_status
 */
class m170923_140919_add_status_key_column_to_delivery_status extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn('{{%delivery_status}}', 'status_key', $this->text()->after('status'));
    }

    public function down()
    {
        $this->dropColumn('{{%delivery_status}}', 'status_key');
    }
}
