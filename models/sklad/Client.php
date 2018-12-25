<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%Client}}".
 *
 * @property int $SN
 * @property string $LName
 * @property string $FName
 * @property string $MName
 * @property string $Phone1
 * @property string $Phone2
 * @property string $Phone3
 *
 * @property Order[] $orders
 */
class Client extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%Client}}';
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
            [['LName', 'FName', 'MName', 'Phone1', 'Phone2', 'Phone3'], 'string'],
            [['FName', 'Phone1'], 'required'],
            [['FName', 'Phone1'], 'unique', 'targetAttribute' => ['FName', 'Phone1']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['SNClient' => 'SN']);
    }
}
