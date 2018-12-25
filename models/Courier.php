<?php

namespace app\models;

use app\behaviors\RelationSaveBehavior;
use app\delivery\Deliveries;
use app\delivery\DeliveryHelper;
use app\models\queries\RegistryQuery;
use app\widgets\Html;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%courier}}".
 *
 * @property int $id
 * @property int $number
 * @property int $main_courier_id
 * @property string $registry_label_url
 * @property string $carrier_key
 * @property int $warehouse_id
 * @property int $pickup_date
 * @property string $pickup_time_start
 * @property string $pickup_time_end
 * @property int $courier_call
 * @property string $class_name_provider
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Order[] $orders
 * @property Warehouse $warehouse
 */
class Courier extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%courier}}';
    }

    /**
     * @inheritdoc
     * @return RegistryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RegistryQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class'     => RelationSaveBehavior::className(),
                'relations' => [
                    'orders' => [
                        'value' => 'orders',
                        'type'  => RelationSaveBehavior::HAS_MANY_TYPE,
                        'link'  => function (Courier $courier, Order $order) {
                            if ($courier->dirtyAttributes) {
                                $courier->save();
                            }
                            (new Query())->createCommand()->update('{{%order}}', ['courier_id' => $courier->id], ['id' => $order->id])->execute();
                        },
                    ],
                ]
            ],

        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['number', 'main_courier_id', 'warehouse_id', 'pickup_date', 'courier_call', 'created_at', 'updated_at'], 'integer'],
            [['registry_label_url'], 'string'],
            [['carrier_key'], 'string', 'max' => 128],
            [['class_name_provider'], 'string', 'max' => 255],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::className(), 'targetAttribute' => ['warehouse_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'number'             => Yii::t('app', 'Registry Number'),
            'main_courier_id'    => Yii::t('app', 'Main Courier Id'),
            'registry_label_url' => Yii::t('app', 'Registry Label Url'),
            'name'               => Yii::t('app', 'Registry Name'),
            'carrier_key'        => Yii::t('app', 'Carrier Key'),
            'orders_count'       => Yii::t('app', 'Orders Count'),
            'warehouse_id'       => Yii::t('app', 'Warehouse ID'),
            'warehouse'          => Yii::t('app', 'Warehouse'),
            'pickup_date'        => Yii::t('app', 'Pickup Date'),
            'pickup_time_start'  => Yii::t('app', 'Pickup time start'),
            'pickup_time_end'    => Yii::t('app', 'Pickup time end'),
            'courier_call'       => Yii::t('app', 'Courier Confirm'),
            'created_at'         => Yii::t('app', 'Created At'),
            'updated_at'         => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @param $orders Order[]
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::className(), ['id' => 'warehouse_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['courier_id' => 'id']);
    }

    public function call()
    {
        $courierId = null;
        if (ArrayHelper::getValue(Yii::$app->params, 'apiship.callCourier')) {
            /** @var Deliveries $deliveries */
            $deliveries = Yii::createObject(Deliveries::className(), [null, null, $this]);
            if ($this->carrier_key !== 'dpd') {
                $courierId = $deliveries->callCourier();
            }

            $this->number = $courierId;
            $this->courier_call = 1;

            if ($this->save()) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function setRegistries(): bool
    {
        if (ArrayHelper::getValue(Yii::$app->params, 'apiship.callCourier')) {
            /** @var Deliveries $deliveries */
            $deliveries = Yii::createObject(Deliveries::className(), [null, null, $this]);
            if ($deliveries->getRegistries()) {
                return true;
            }
            return false;
        }
        return true;
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['created_at'] = function () {
            return date('Y-m-d H:i:s', $this->created_at);
        };

        $fields['updated_at'] = function () {
            return date('Y-m-d H:i:s', $this->updated_at);
        };

        $fields['pickup_date'] = function () {
            return date('Y-m-d H:i:s', $this->pickup_date);
        };

        $fields['registry_label_url'] = function () {
            return Url::to(['/courier/download', 'id' => $this->id], true);
        };

        unset($fields['number']);
        unset($fields['class_name_provider']);

        return $fields;
    }

    public function getOrdersProviredIds()
    {
        if ($this->orders === null) {
            $this->orders = $this->getOrders()->all();
        }

        $orderIds = [];
        foreach ($this->orders as $order) {
            $orderIds[] = $order->provider_number;
        }

        return $orderIds;
    }

    /**
     * Получение вызванных курьеров для склада на определенную дату
     *
     * @param int $warehouseId
     * @param string $carrierKey
     * @param int $pickupDate
     * @param Order[] $orders
     * @return Courier[]
     */
    public function getActiveCourierCall(
        int $warehouseId,
        string $carrierKey,
        int $pickupDate,
        array $orders = []
    ) {

        $couriers = Courier::find()
            ->joinWith(['orders'])
            ->where([
                'courier.carrier_key'  => $carrierKey,
                'courier.pickup_date'  => $pickupDate,
                'courier.warehouse_id' => $warehouseId
            ])
            ->all();

        $result = [];
        foreach ($couriers as $courier) {
            foreach ($courier->orders as $order) {
                if (!isset($result[$order->shop_id])) {
                    $result[$order->shop_id] = $courier;
                }
            }
            if (is_null($courier->main_courier_id) && empty($result['main'])) {
                $result['main'] = $courier;
            }
        }
        return $result;

    }

    /**
     * @param $order
     * @param $courier
     * @param string $email
     * @return bool
     */
    public function sendCourierNotification($order, $courier, $email = 'l.derjugina@maxipost.ru')
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'newCourier-html'],
                ['order' => $order, 'courier' => $courier]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($email)
            ->setCc('khayrtdinov@fastery.ru')
            ->setSubject('Вызов курьера ' . Yii::$app->name)
            ->send();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getOrdersCount(): int
    {
        return count($this->orders);
    }

    /**
     * @return string
     */
    public function getCarrierKey(): ?string
    {
        return DeliveryHelper::getName($this->carrier_key);
    }

    /**
     * @return string
     */
    public function getWarehouseName(): string
    {
        return ($this->warehouse) ? $this->warehouse->address->full_address : '';
    }

    /**
     * @return string
     */
    public function getPickupDate(): string
    {
        return date('d.m.Y', $this->pickup_date);
    }

    /**
     * @param bool $html
     * @return string
     */
    public function getCourierCall(bool $html = true): string
    {
        if ($html) {
            if ($this->courier_call) {
                return Html::tag(
                    'div',
                    Yii::t('app', 'Yes'),
                    ['class' => 'label label-success']
                );
            }
            return Html::tag(
                'div',
                Yii::t('app', 'No'),
                ['class' => 'label label-success']
            );

        } else {
            if ($this->courier_call) {
                return Yii::t('app', 'Yes');
            }
            return Yii::t('app', 'No');
        }
    }

    /**
     * @return string
     */
    public function getShopName(): string
    {
        return (count($this->orders)) ? $this->orders[0]->shop->name : '';
    }

    /**
     * @return string
     */
    public function getPrintUrl(): string
    {
        return Url::to(['courier/download', 'id' => $this->id]);
    }

    /**
     * @return string
     */
    public function getPrintText(): string
    {
        return '<i class="fa fa-save"></i>';
    }
}
