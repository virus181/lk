<?php

use yii\db\Migration;

class m170319_131212_rename_column_accound_id_to_product_table extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_product_account_id', '{{%product}}');
        $this->renameColumn('{{%product}}', 'account_id', 'shop_id');
        $this->addForeignKey('fk_product_shop_id', '{{%product}}', 'shop_id', '{{%shop}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_product_shop_id', '{{%product}}');
        $this->renameColumn('{{%product}}', 'account_id', 'shop_id');
        $this->addForeignKey('fk_product_account_id', '{{%product}}', 'shop_id', '{{%shop}}', 'id');
    }
}
