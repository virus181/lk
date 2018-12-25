<?php

use yii\db\Migration;

/**
 * Handles adding owner_id to table `log`.
 */
class m171018_103752_add_owner_id_column_to_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%log}}', 'owner_id', $this->integer()->after('model_id'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%log}}', 'owner_id');
    }
}
