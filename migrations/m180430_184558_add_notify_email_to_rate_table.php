<?php

use yii\db\Migration;

/**
 * Class m180430_184558_add_notify_email_to_rate_table
 */
class m180430_184558_add_notify_email_to_rate_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%rate}}',
            'notify_email',
            $this->string()->after('type')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%rate}}', 'notify_email');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180430_184558_add_notify_email_to_rate_table cannot be reverted.\n";

        return false;
    }
    */
}
