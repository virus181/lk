<?php

use yii\db\Migration;

/**
 * Handles adding kpp to table `shop`.
 */
class m171016_083839_add_kpp_column_to_shop_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%shop}}', 'kpp', $this->string(16)->after('inn'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%shop}}', 'kpp');
    }
}
