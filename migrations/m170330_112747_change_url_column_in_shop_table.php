<?php

use yii\db\Migration;

class m170330_112747_change_url_column_in_shop_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%shop}}', 'url', $this->char(255)->after('name'));
    }

    public function down()
    {
        $this->alterColumn('{{%shop}}', 'url', $this->char(255)->notNull()->after('name'));
    }
}
