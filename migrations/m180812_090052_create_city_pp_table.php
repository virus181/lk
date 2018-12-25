<?php

use yii\db\Migration;

/**
 * Handles the creation of table `city_pp`.
 */
class m180812_090052_create_city_pp_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('city_pp', [
            'id' => $this->integer(),
            'full_name' => $this->string(255),
            'owner_id' => $this->integer(),
            'code' => $this->string(32),
            'name' => $this->string(128),
            'region' => $this->string(64),
            'city_fias_id' =>$this->string(64)
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('city_pp');
    }
}


