<?php

use yii\db\Migration;

class m170327_082430_add_count_columnt_to_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%product}}', 'count', $this->integer()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%product}}', 'count');
    }
}
