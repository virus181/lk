<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%Product}}".
 *
 * @property int $SN
 * @property string $Name
 *
 * @property Catalog[] $catalogs
 * @property Market[] $markets
 */
class Product extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%Product}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        /** @var Connection $connection */
        $connection = Yii::$app->get('db_sklad');
        return $connection;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Name'], 'required'],
            [['Name'], 'string'],
            [['Name'], 'unique'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogs()
    {
        return $this->hasOne(Catalog::className(), ['SNProduct' => 'SN']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMarkets()
    {
        return $this->hasOne(Market::className(), ['SN' => 'SNMarket'])->viaTable('{{%Catalog}}', ['SNProduct' => 'SN']);
    }
}
