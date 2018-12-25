<?php

use yii\db\Migration;

/**
 * Class m170808_194006_add_fulfillment_column_to_shop
 */
class m170808_194006_add_fulfillment_column_to_shop extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%shop}}', 'fulfillment', $this->boolean()->defaultValue(NULL));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%shop}}', 'fulfillment');
    }
}
