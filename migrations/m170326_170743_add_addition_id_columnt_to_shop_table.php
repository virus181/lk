<?php

use yii\db\Migration;

class m170326_170743_add_addition_id_columnt_to_shop_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%shop}}', 'additional_id', $this->integer()->after('id'));
        $this->createIndex('idx_shop_additional_id', '{{%shop}}', 'additional_id');
    }

    public function down()
    {
        $this->dropIndex('idx_shop_additional_id', '{{%shop}}');
        $this->dropColumn('{{%shop}}', 'additional_id');
    }
}
