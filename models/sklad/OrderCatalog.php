<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%OrderCatalog}}".
 *
 * @property int $SN
 * @property int $SNOrder
 * @property int $SNCatalog
 * @property int $Count
 *
 * @property Catalog $sNCatalog
 * @property Order $sNOrder
 */
class OrderCatalog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%OrderCatalog}}';
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
            [['SNOrder', 'SNCatalog', 'Count'], 'required'],
            [['SNOrder', 'SNCatalog', 'Count'], 'integer'],
            [['SNCatalog'], 'exist', 'skipOnError' => true, 'targetClass' => Catalog::className(), 'targetAttribute' => ['SNCatalog' => 'SN']],
            [['SNOrder'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['SNOrder' => 'SN']],
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNCatalog()
    {
        return $this->hasOne(Catalog::className(), ['SN' => 'SNCatalog']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNOrder()
    {
        return $this->hasOne(Order::className(), ['SN' => 'SNOrder']);
    }
}
