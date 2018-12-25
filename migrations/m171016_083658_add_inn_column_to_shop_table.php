<?php

use yii\db\Migration;

/**
 * Handles adding inn to table `shop`.
 */
class m171016_083658_add_inn_column_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%shop}}', 'inn', $this->string(16)->after('phone'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%shop}}', 'inn');
    }
}
