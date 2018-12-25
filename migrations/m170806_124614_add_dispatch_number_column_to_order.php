<?php

use yii\db\Migration;

class m170806_124614_add_dispatch_number_column_to_order extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'dispatch_number', $this->char(255)->after('provider_number'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order}}', 'dispatch_number');
    }
}
