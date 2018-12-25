<?php

use yii\db\Migration;

/**
 * Handles adding created_at to table `{{%product}}`.
 */
class m170327_082624_add_created_at_column_to_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%product}}', 'created_at', $this->integer()->notNull());
        $this->addColumn('{{%product}}', 'updated_at', $this->integer()->notNull());
    }

    public function down()
    {
        $this->dropColumn('{{%product}}', 'updated_at');
        $this->dropColumn('{{%product}}', 'created_at');
    }
}
