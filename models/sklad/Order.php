<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%Order}}".
 *
 * @property int $SN
 * @property string $Name
 * @property int $SNMarket
 * @property int $SNShipping
 * @property int $SNClient
 * @property int $SNAddress
 * @property int $SNStatus
 * @property string $Date
 * @property string $DateShip
 * @property string $Comment
 * @property string $ProductPrice
 * @property string $ShippingPrice
 *
 * @property Address $sNAddress
 * @property Client $sNClient
 * @property Market $sNMarket
 * @property Shipping $sNShipping
 * @property Status $sNStatus
 * @property OrderCatalog[] $orderCatalogs
 * @property Catalog[] $sNCatalogs
 * @property OrderStatus[] $orderStatuses
 */
class Order extends ActiveRecord
{
    public $shipingIds = [
        'cdek' => 10,
        'b2cpl' => 7,
        'dpd' => 2,
        'boxberry' => 5,
        'dalli' => 14,
        'iml' => 2038,
        'own' => 36,
        'viehali' => 2043,
        'maxipost' => 1036,
        'on-time' => 2050,
        'easyway' => 2052,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%Order}}';
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
            [['Name', 'SNMarket', 'SNShipping', 'SNStatus'], 'required'],
            [['Name', 'Comment'], 'string'],
            [['SNMarket', 'SNShipping', 'SNClient', 'SNAddress', 'SNStatus'], 'integer'],
            [['Date', 'DateShip'], 'safe'],
            [['ProductPrice', 'ShippingPrice'], 'number'],
            [['Name'], 'unique', 'targetAttribute' => ['Name', 'SNMarket'], 'message' => Yii::t('app', 'Номер заказ в складской программе должен быть уникален для выбранного магазина')],
            [['SNAddress'], 'exist', 'skipOnError' => true, 'targetClass' => Address::className(), 'targetAttribute' => ['SNAddress' => 'SN']],
            [['SNClient'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['SNClient' => 'SN']],
            [['SNMarket'], 'exist', 'skipOnError' => true, 'targetClass' => Market::className(), 'targetAttribute' => ['SNMarket' => 'SN']],
            [['SNShipping'], 'exist', 'skipOnError' => true, 'targetClass' => Shipping::className(), 'targetAttribute' => ['SNShipping' => 'SN']],
            [['SNStatus'], 'exist', 'skipOnError' => true, 'targetClass' => Status::className(), 'targetAttribute' => ['SNStatus' => 'SN']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNAddress()
    {
        return $this->hasOne(Address::className(), ['SN' => 'SNAddress']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNClient()
    {
        return $this->hasOne(Client::className(), ['SN' => 'SNClient']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNMarket()
    {
        return $this->hasOne(Market::className(), ['SN' => 'SNMarket']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNShipping()
    {
        return $this->hasOne(Shipping::className(), ['SN' => 'SNShipping']);
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
    public function getOrderCatalogs()
    {
        return $this->hasMany(OrderCatalog::className(), ['SNOrder' => 'SN']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSNCatalogs()
    {
        return $this->hasMany(Catalog::className(), ['SN' => 'SNCatalog'])->viaTable('{{%OrderCatalog}}', ['SNOrder' => 'SN']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderStatuses()
    {
        return $this->hasMany(OrderStatus::className(), ['SNOrder' => 'SN']);
    }

    /**
     * @param \app\models\Order $order
     * @return string|null
     */
    public function getShippingId(\app\models\Order $order)
    {
        return ArrayHelper::getValue($this->shipingIds, $order->delivery->carrier_key);
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function getShippingKey(int $id): ?string
    {
        $key = array_search($id, $this->shipingIds);
        return $key !== false ? $key : null;
    }
}
