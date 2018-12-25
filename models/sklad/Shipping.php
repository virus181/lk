<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%Shipping}}".
 *
 * @property int $SN
 * @property string $Name
 *
 * @property Order[] $orders
 */
class Shipping extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%Shipping}}';
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
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['SNShipping' => 'SN']);
    }
}
