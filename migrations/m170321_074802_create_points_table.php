<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%points}}`.
 */
class m170321_074802_create_points_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%points}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text(),
            'code' => $this->char(255)->notNull(),
            'additionalCode' => $this->char(255)->notNull(),
            'cod' => $this->integer()->notNull()->defaultValue(0),
            'card' => $this->integer()->notNull()->defaultValue(0),
            'address' => $this->text()->notNull(),
            'cityGuid' => $this->char(255),
            'phone' => $this->text(),
            'timetable' => $this->text(),
            'lat' => $this->char(255),
            'lng' => $this->char(255),
            'className' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('index_code', '{{%points}}', 'code', true);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('index_code', '{{%points}}');
        $this->dropTable('{{%points}}');
    }
}
