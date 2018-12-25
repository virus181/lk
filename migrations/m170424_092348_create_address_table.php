<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%address}}`.
 */
class m170424_092348_create_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%address}}', [
            'id' => $this->primaryKey(),
            'country' => $this->string(255),
            'region' => $this->string(255),
            'region_fias_id' => $this->string(255),
            'city' => $this->string(255),
            'city_fias_id' => $this->string(255),
            'street' => $this->string(255),
            'street_fias_id' => $this->string(255),
            'house' => $this->char(10),
            'flat' => $this->char(10),
            'housing' => $this->char(10),
            'building' => $this->char(10),
            'postcode' => $this->char(10),
            'full_address' => $this->char(10),
            'address_object' => $this->text(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%address}}');
    }
}
