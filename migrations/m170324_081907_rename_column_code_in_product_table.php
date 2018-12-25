<?php

use yii\db\Migration;

class m170324_081907_rename_column_code_in_product_table extends Migration
{
    public function up()
    {
        $this->renameColumn('{{%product}}', 'code', 'barcode');
    }

    public function down()
    {
        $this->renameColumn('{{%product}}', 'barcode', 'code');
    }
}
