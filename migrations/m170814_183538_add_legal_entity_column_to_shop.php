<?php

use yii\db\Migration;

/**
 * Class m170814_183538_add_legal_entity_column_to_shop
 */
class m170814_183538_add_legal_entity_column_to_shop extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%shop}}', 'legal_entity', $this->char(255)->after('url'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%shop}}', 'legal_entity');
    }
}
