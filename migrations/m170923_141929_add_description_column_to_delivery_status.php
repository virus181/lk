<?php

use yii\db\Migration;

/**
 * Class m170923_141929_add_description_column_to_delivery_status
 */
class m170923_141929_add_description_column_to_delivery_status extends Migration
{
    public function up()
    {
        $this->addColumn('{{%delivery_status}}', 'description', $this->text()->after('status'));
    }

    public function down()
    {
        $this->dropColumn('{{%delivery_status}}', 'description');
    }
}
