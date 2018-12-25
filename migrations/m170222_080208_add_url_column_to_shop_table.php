<?php

use yii\db\Migration;

/**
 * Handles adding url to table `{{%shop}}`.
 */
class m170222_080208_add_url_column_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%shop}}', 'url', $this->char(255)->notNull()->after('name'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%shop}}', 'url');
    }
}
