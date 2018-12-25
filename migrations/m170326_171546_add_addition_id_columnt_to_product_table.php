<?php

use yii\db\Migration;

class m170326_171546_add_addition_id_columnt_to_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%product}}', 'additional_id', $this->integer()->after('id'));
        $this->createIndex('idx_product_additional_id', '{{%product}}', 'additional_id');
    }

    public function down()
    {
        $this->dropIndex('idx_product_additional_id', '{{%product}}');
        $this->dropColumn('{{%product}}', 'additional_id');
    }
}
