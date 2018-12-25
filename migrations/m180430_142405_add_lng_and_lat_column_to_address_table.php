<?php

use yii\db\Migration;

/**
 * Handles adding lng_and_lat to table `address`.
 */
class m180430_142405_add_lng_and_lat_column_to_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%address}}',
            'lat',
            $this->float()->after('postcode')
        );
        $this->addColumn(
            '{{%address}}',
            'lng',
            $this->float()->after('lat')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%address}}', 'lat');
        $this->dropColumn('{{%address}}', 'lng');
    }
}
