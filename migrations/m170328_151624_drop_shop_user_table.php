<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%shop_user}}`.
 */
class m170328_151624_drop_shop_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropTable('{{%shop_user}}');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
