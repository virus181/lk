<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%Address}}".
 *
 * @property int $SN
 * @property string $Settlement
 * @property string $Region
 * @property string $Street
 * @property string $House
 * @property string $Build
 * @property string $Entrance
 * @property int $Floor
 * @property string $Flat
 *
 * @property Order[] $orders
 */
class Address extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%Address}}';
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
            [['Settlement', 'House'], 'required'],
            [['Settlement', 'Region', 'Street', 'House', 'Build', 'Entrance', 'Flat'], 'string'],
            [['Floor'], 'integer'],
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['SNAddress' => 'SN']);
    }
}
