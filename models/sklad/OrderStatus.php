<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%OrderStatus}}".
 *
 * @property int $SN
 * @property int $SNOrder
 * @property int $SNStatus
 * @property string $Date
 * @property int $SNUser
 *
 * @property Order $sNOrder
 * @property Status $sNStatus
 * @property User $sNUser
 */
class OrderStatus extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%OrderStatus}}';
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
            [['SNStatus'], 'default', 'value' => 1],
            [['Date'], 'default', 'value' => date('Y-m-d H:i:s', time())],
            [['SNUser'], 'default', 'value' => 25],
            [['SNOrder', 'SNStatus', 'Date'], 'required'],
            [['SNOrder', 'SNStatus', 'SNUser'], 'integer'],
            [['Date'], 'safe'],
            [['SNOrder'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['SNOrder' => 'SN']],
            [['SNStatus'], 'exist', 'skipOnError' => true, 'targetClass' => Status::className(), 'targetAttribute' => ['SNStatus' => 'SN']],
            [['SNUser'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['SNUser' => 'SN']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNOrder()
    {
        return $this->hasOne(Order::className(), ['SN' => 'SNOrder']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNStatus()
    {
        return $this->hasOne(Status::className(), ['SN' => 'SNStatus']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNUser()
    {
        return $this->hasOne(User::className(), ['SN' => 'SNUser']);
    }
}
