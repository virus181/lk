<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%User}}".
 *
 * @property int $SN
 * @property string $Login
 * @property string $Password
 * @property string $LName
 * @property string $FName
 * @property string $MName
 *
 * @property OrderStatus[] $orderStatuses
 */
class User extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%User}}';
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
            [['Login', 'Password', 'LName', 'FName'], 'required'],
            [['Login', 'Password', 'LName', 'FName', 'MName'], 'string'],
            [['Login'], 'unique'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderStatuses()
    {
        return $this->hasMany(OrderStatus::className(), ['SNUser' => 'SN']);
    }
}
