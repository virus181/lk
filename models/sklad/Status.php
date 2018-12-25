<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%Status}}".
 *
 * @property int $SN
 * @property string $Name
 *
 * @property Order[] $orders
 * @property OrderStatus[] $orderStatuses
 */
class Status extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%Status}}';
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
            [['SN'], 'required'],
            [['SN'], 'integer'],
            [['Name'], 'string'],
            [['Name'], 'unique'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['SNStatus' => 'SN']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderStatuses()
    {
        return $this->hasMany(OrderStatus::className(), ['SNStatus' => 'SN']);
    }
}
