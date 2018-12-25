<?php

use yii\db\Migration;

/**
 * Handles adding shop_id to table `{{%order}}`.
 */
class m170319_134231_add_shop_id_column_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'shop_id', $this->integer()->notNull()->after('id'));
        $this->addForeignKey('fk_order_shop_id', '{{%order}}', 'shop_id', '{{%shop}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_order_shop_id', '{{%order}}');
        $this->dropColumn('{{%order}}', 'shop_id');
    }
}
