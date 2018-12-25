<?php

use yii\db\Migration;

/**
 * Class m180519_171135_add_owner_id_index_to_log_table
 */
class m180519_171135_add_owner_id_index_to_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('inx_owner_id', 'log', 'owner_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('inx_owner_id', 'log');
    }

}
