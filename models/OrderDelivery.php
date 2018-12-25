<?php

namespace app\models;

use app\behaviors\LogBehavior;
use app\delivery\Deliveries;
use app\delivery\DeliveryHelper;
use app\models\Helper\Date;
use app\models\queries\OrderDeliveryQuery;
use app\models\Helper\Phone;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%order_delivery}}".
 *
 * @property int $id
 * @property int $order_id
 * @property string $city
 * @property string $city_fias_id
 * @property string $type
 * @property integer $point_id
 * @property string $point_type
 * @property string $pickup_point_id
 * @property string $pickup_type
 * @property array|string $pickup_types
 * @property integer $pickup_date
 * @property integer $delivery_date
 * @property string $time_start
 * @property string $time_end
 * @property string $pickup_address
 * @property string $carrier_key
 * @property string $tariff_id
 * @property string $name
 * @property int $min_term
 * @property int $max_term
 * @property string $class_name_provider
 * @property double $cost
 * @property double $original_cost
 * @property double $charge
 * @property double $real_cost
 * @property boolean $partial
 * @property string $phone
 * @property string $provider
 * @property Points $point
 * @property string $point_address
 * @property int $created_at
 * @property int $updated_at
 *
 * @property string $deliveryName
 *
 * @property Order $order
 */
class OrderDelivery extends ActiveRecord
{
    const DELIVERY_TO_DOOR = 'courier';
    const DELIVERY_TO_POINT = 'point';
    const DELIVERY_POST = 'mail';
    const PICKUP_TYPE_FROM_DOOR = 'from_door';
    const PICKUP_TYPE_ON_TERMINAL = 'on_terminal';
    const POINT_TYPE_PVZ = 'pvz';
    const POINT_TYPE_POSTAMAT = 'postamat';
    const POINT_TYPE_MAIL = 'mail';
    const POINT_TYPE_TERMINAL = 'terminal';

    const SCENARIO_CALCULATE_API = 'calculateApi';
    const SCENARIO_APPLY_DELIVERY = 'applyDelivery';

    const MIN_DELIVERY_TIME = 10;
    const MAX_DELIVERY_TIME = 19;

    const FASTEST_TARIFFS = [
        'viehali' => 212
    ];

    public $lat;
    public $lng;
    public $city_fias_id;
    public $disabledEdit = false;
    /** @var string */
    private $_point_address;
    /** @var string */
    private $provider;
    private $_uid;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_delivery}}';
    }

    /**
     * @inheritdoc
     * @return OrderDeliveryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderDeliveryQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            LogBehavior::className(),
        ];
    }

    public function init()
    {
        if (date('N', time()) == 5) {
            $this->pickup_date = strtotime('+3 days', time());
        } else {
            $this->pickup_date = strtotime('+1 days', time());
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['pickup_date'], 'default', 'value' => function () {
                if (date('N', time()) == 5) {
                    return strtotime('+3 days', time());
                } else {
                    return strtotime('+1 days', time());
                }
            }],
            [['pickup_date'], 'filter', 'filter' => function ($value) {
                if (is_string($value) && (strpos($value, '-') !== false || strpos($value, '.') !== false)) {
                    return strtotime($value);
                } elseif ($value === null) {
                    return time();
                } else {
                    return $value;
                }
            }],
            [['pickup_date'], 'validatePickupDate'],
            [['delivery_date'], 'validateDeliveryDate'],

            // Валидаторы для времени доставки
            [['time_start', 'time_end'], 'date', 'format' => 'php:H:i:s'],
            [['time_start'], 'validateDeliveryStartTime'],
            [['time_end'], 'validateDeliveryEndTime'],

            [['cost'], 'filter', 'filter' => function ($value) {
                return str_replace(',', '.', $value);
            }],
            [['cost', 'charge'], 'number', 'min' => 0],
            [['pickup_date'], 'integer'],
            [['pickup_date', 'cost'], 'required'],
            [['city', 'city_fias_id'], 'string', 'max' => 255],
            [['tariff_id', 'type', 'partial'], 'safe'],
        ];
    }

    /**
     * @param $attribute
     */
    public function validatePickupDate($attribute)
    {
        $dayOfWeek = date('N', $this->pickup_date);
        if ($dayOfWeek > 5 && $dayOfWeek < 1) {
            $this->addError($attribute, Yii::t('app', 'Указана некорректная дата отгрузки'));
        }
    }

    /**
     * Проверка даты доставки (Дата доставки не может быть меньше даты забора + минимальный срок доставки)
     * @param $attribute
     */
    public function validateDeliveryDate($attribute)
    {
        if (!empty($this->min_term)) {
            if ($this->delivery_date < strtotime('+' . $this->min_term . ' days', $this->pickup_date)) {
                $this->addError($attribute, Yii::t('app', 'Delivery date is wrong'));
            }
        }
    }

    /**
     * Проверка времени доставки
     * @param $attribute
     */
    public function validateDeliveryStartTime($attribute)
    {
        if (!empty($this->time_start)) {
            if ((int) $this->time_start < self::MIN_DELIVERY_TIME && $this->carrier_key != DeliveryHelper::CARRIER_CODE_PICKPOINT) {
                $this->addError($attribute, Yii::t('order', 'Delivery start time is too small {minTime}', ['minTime' => self::MIN_DELIVERY_TIME]));
            }

            if ($this->carrier_key == DeliveryHelper::CARRIER_CODE_PICKPOINT && !in_array((int) $this->time_start, array_keys(DeliveryHelper::PICKPOINT_TIME_START_INTERVALS))) {
                $this->addError($attribute, Yii::t('order', 'Pickpoint time start must be 09:00 or 14:00'));
            }
        }
    }

    /**
     * Проверка времени доставки
     * @param $attribute
     */
    public function validateDeliveryEndTime($attribute)
    {
        if (!empty($this->time_end)) {
            $deliveryInterval = ((int) $this->time_end) - ((int) $this->time_start);
            if ($deliveryInterval <= 3) {
                $this->addError($attribute, Yii::t('order', 'Delivery time interval is too short'));
            }

            if ((int) $this->time_end > self::MAX_DELIVERY_TIME) {
                $this->addError($attribute, Yii::t('order', 'Delivery end time is too mach {maxTime}', ['maxTime' => self::MAX_DELIVERY_TIME]));
            }

            if ($this->carrier_key == DeliveryHelper::CARRIER_CODE_PICKPOINT
                && isset(DeliveryHelper::PICKPOINT_TIME_START_INTERVALS[(int)$this->time_start])
                && (int)$this->time_end != DeliveryHelper::PICKPOINT_TIME_START_INTERVALS[(int)$this->time_start]
            ) {
                $this->addError($attribute, Yii::t('order', 'PickPoint delivery interval must be 09:00 - 14:00 or 14:00 - 18:00'));
            }
        }
    }

    /**
     * @param Order $order
     * @param array $params
     * @return bool
     */
    public function loadDelivery(Order $order, array $params = [])
    {
        $deliveryLoaded = false;

        $orderDeliveryModel = new OrderDelivery();
        $orderDeliveryModel->setScenario(OrderDelivery::SCENARIO_CALCULATE_API);

        $calculator = $order->getCalculator();

        if ($calculator->prepareData() && $calculator->validate()) {
            /** @var Deliveries $deliveries */
            $deliveries = Yii::createObject(\app\delivery\Delivery::className(), [
                $calculator,
                $orderDeliveryModel
            ]);

            $orderDeliveries = $deliveries->calculate();

            foreach ($orderDeliveries as $orderDelivery) {
                if ($orderDelivery->getUid() === $this->getUid()) {
                    $except = ['id'];
                    if ($this->cost !== null) {
                        $except[] = 'cost';
                    }
                    if ($this->pickup_date !== null) {
                        $except[] = 'pickup_date';
                    }

                    $this->setAttributes($orderDelivery->getAttributes(null, $except), false);
                    $this->point_address = $orderDelivery->point_address;
                    $this->partial = (int) $order->partial;
                    $this->lat = $orderDelivery->lat;
                    $this->lng = $orderDelivery->lng;
                    if (isset(array_flip($this->pickup_types)[OrderDelivery::PICKUP_TYPE_FROM_DOOR])) {
                        $this->pickup_type = OrderDelivery::PICKUP_TYPE_FROM_DOOR;
                    } else {
                        $this->pickup_type = OrderDelivery::PICKUP_TYPE_ON_TERMINAL;
                        $this->pickup_point_id = isset(array_keys($this->getPickupPoints())[0]) ? array_keys($this->getPickupPoints())[0] : null;
                    }
                    $deliveryLoaded = true;
                }
            }

            if(!$deliveryLoaded && $this->type == 'mail') {
                foreach ($orderDeliveries as $orderDelivery) {
                    if ($orderDelivery->tariff_id === DeliveryHelper::$mailTariffMapper[$this->tariff_id]) {
                        $except = ['id'];
                        if ($this->cost !== null) {
                            $except[] = 'cost';
                        }
                        if ($this->pickup_date !== null) {
                            $except[] = 'pickup_date';
                        }

                    $this->setAttributes($orderDelivery->getAttributes(null, $except), false);
                    $this->point_address = $orderDelivery->point_address;
                    $this->partial = (int) $order->partial;
                    $this->lat = $orderDelivery->lat;
                    $this->lng = $orderDelivery->lng;
                    if (isset(array_flip($this->pickup_types)[OrderDelivery::PICKUP_TYPE_FROM_DOOR])) {
                        $this->pickup_type = OrderDelivery::PICKUP_TYPE_FROM_DOOR;
                    } else {
                        $this->pickup_type = OrderDelivery::PICKUP_TYPE_ON_TERMINAL;
                    }
                    $deliveryLoaded = true;
                }
            }
        }

            if (!empty($params) && $deliveryLoaded) {
                $this->pickup_date = (new Date(time()))->getNearestPickupDate(
                    (int)$order->shop->process_day
            );
            $this->delivery_date = (new Date($this->pickup_date))
                ->getNearestDeliveryDate(
                $this->min_term,
                isset($params['delivery_date']) ? strtotime($params['delivery_date']) : 0
            );
            $this->time_start = (new Date(time()))->setFormat('H:i:s')->getTime(
                OrderDelivery::MIN_DELIVERY_TIME,
                $params['time_start'] ?? ''
            );
            $this->time_end = (new Date(time()))->setFormat('H:i:s')->getTime(
                OrderDelivery::MAX_DELIVERY_TIME,
                $params['time_end'] ?? ''
            );
        }

            if (!$deliveryLoaded) {
                $this->addError('uid', Yii::t('app', 'Указан несуществующий UID'));
            }
        }

        return $deliveryLoaded;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        $attributes = $this->getAttributes([
            'type',
            'point_id',
            'point_type',
            'carrier_key',
            'tariff_id',
            'class_name_provider',
            'pickup_types',
        ]);

        if (is_array($attributes['pickup_types'])) {
            $attributes['pickup_types'] = implode(',', $attributes['pickup_types']);
        }

        if ($this->_uid === null) {
            $this->_uid = md5(implode(',', $attributes));
        }

        return $this->_uid;
    }

    public function setUid($uid)
    {
        $this->_uid = $uid;
    }

    /**
     * @return array
     */
    public function getPickupPoints()
    {
        $query = Points::find()
            ->select(['point_id', 'address'])
            ->andWhere([
                'carrier_key' => $this->carrier_key,
                'class_name' => $this->class_name_provider,
            ])
            ->andFilterWhere(['in', 'available_operation', [1, 3]])
            ->andFilterWhere(['like', 'address', $this->city]);

        $query->createCommand()->getRawSql();

        $points = $query->asArray()->all();

        return ArrayHelper::map($points, 'point_id', 'address');
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_APPLY_DELIVERY] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_CALCULATE_API] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    public function getPoint_address()
    {
        if ($this->_point_address === null) {
            if ($this->point !== null) {
                $this->_point_address = $this->point->address;
            }
        }

        return $this->_point_address;
    }

    public function setPoint_address($point_address)
    {
        $this->_point_address = $point_address;
    }

    public function getPoint()
    {
        return $this->hasOne(Points::className(), ['point_id' => 'point_id']);
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['uid'] = function () {
            return $this->getUid();
        };

        unset($fields['class_name_provider']);
        unset($fields['tariff_id']);
        unset($fields['pickup_type']);
        unset($fields['pickup_types']);
        unset($fields['pickup_point_id']);
        unset($fields['city']);
        unset($fields['city_fias_id']);
        unset($fields['charge']);


        if ($this->scenario !== self::SCENARIO_CALCULATE_API) {
            $fields['delivery_date'] = function () {
                return date('Y-m-d', $this->delivery_date);
            };
            $fields['pickup_date'] = function () {
                return date('Y-m-d', $this->pickup_date);
            };
            $fields['created_at'] = function () {
                return date('Y-m-d H:i:s', $this->created_at);
            };

            $fields['updated_at'] = function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            };

            $fields['phone'] = function () {
                return (new Phone($this->phone ?? ''))->getHumanView();
            };

            $fields['cost'] = function () {
                return (float) $this->cost;
            };

            $fields['original_cost'] = function () {
                return (float) $this->original_cost;
            };

        } else {
            unset($fields['pickup_date']);
            unset($fields['original_cost']);
            unset($fields['charge']);
        }

        if ($this->point_id) {
            $fields['point_address'] = 'point_address';
            $fields['lat'] = 'lat';
            $fields['lng'] = 'lng';
            $fields['point_id'] = 'point_id';
            $fields['point_type'] = 'point_type';

            $fields['phone'] = function () {
                return (new Phone($this->phone ?? ''))->getHumanView();
            };
        } else {
            unset($fields['point_id']);
            unset($fields['point_type']);
            unset($fields['phone']);
        }

        if (Yii::$app->params['environment'] == 'api') {
            unset($fields['id']);
            unset($fields['updated_at']);
            unset($fields['created_at']);
            unset($fields['cl']);
        }

        return $fields;
    }

    public function getProvider()
    {
        if ($this->provider === null && $this->class_name_provider !== null) {
            $this->provider = substr(md5($this->class_name_provider), -6);
        }

        return $this->provider;
    }

    public function setProvider($providerHash)
    {
        /** @var Deliveries $deliveries */
        $deliveries = Yii::createObject(Deliveries::className(), [new Order(), $this]);
        $this->class_name_provider = $deliveries->getClassNameByProviderHash($providerHash);
    }

    public function extraFields()
    {
        $fields = parent::extraFields();
        if ($this->scenario !== self::SCENARIO_CALCULATE_API) {
            $fields['order'] = 'order';
        }

        $fields['pickup_points'] = function () {
            return $this->getPickupPoints();
        };

        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return string
     */
    public function getDeliveryDateFormat(): string
    {
        $minDeliveryDate = $this->pickup_date + $this->min_term * 86400;
        return ($this->delivery_date && $this->delivery_date >= $minDeliveryDate)
            ? date('d.m.Y', $this->delivery_date)
            : date('d.m.Y', strtotime("+" . $this->min_term . " days", $this->pickup_date));

//        return DeliveryHelper::getDeliveryDate($this->pickup_date, $this->min_term, $this->max_term);
    }

    /**
     * Возвращает анонсированные сроки доставки в текстовом формате
     * @return string
     */
    public function getDeliveryTerms()
    {
        if ($this->min_term == $this->max_term && $this->max_term == 0) {
            return Yii::t('app', 'Today');
        }

        $term = sprintf('от %d до %d', $this->min_term ? $this->min_term : 1, $this->max_term ? $this->max_term : 1);
        if ($this->min_term == $this->max_term) {
            $term = sprintf('от %d', $this->min_term ? $this->min_term : 1);
            $term .= Helper::getNumEnding(
                    $this->min_term ? $this->min_term : 1,
                    [' дня', ' дней', ' дней']
                );
        } else {
            $term .= ' дней';
        }
        return $term;
    }

    public function getPickupDateFormat()
    {
        return DeliveryHelper::getPickupDate($this->pickup_date);
    }

    public function getPhone()
    {
        return (new Phone($this->phone))->getHumanView();
    }

    public function getIconPath()
    {
        return DeliveryHelper::getIconPath($this->carrier_key);
    }

    public function getDeliveryName()
    {
        return $this->carrier_key == DeliveryHelper::CARRIER_CODE_OWN ? $this->name : DeliveryHelper::getName($this->carrier_key);
    }

    public function getPickupTypeName()
    {
        $names = $this->getPickUpTypesName();
        return $names[$this->pickup_type];
    }

    public function getPickUpTypesName()
    {
        $types = [
            self::PICKUP_TYPE_ON_TERMINAL => Yii::t('app', 'On terminal'),
            self::PICKUP_TYPE_FROM_DOOR => Yii::t('app', 'From door'),
        ];

        $array = array_merge(array_flip((array)$this->pickup_types), array_intersect_key($types, array_flip((array)$this->pickup_types)));
        return $array;
    }

    public function getDeliveryTypeName()
    {
        $names = $this->getDeliveryTypes();
        return $names[$this->type];
    }

    /**
     * Получение списка доступных типов доставки
     * @return array
     */
    public function getDeliveryTypes()
    {
        $types = [
            self::DELIVERY_TO_DOOR => Yii::t('app', 'Carrier'),
            self::DELIVERY_TO_POINT => Yii::t('app', 'To point'),
            self::DELIVERY_POST => Yii::t('app', 'Mail'),
        ];

        return $types;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->pickup_types = json_encode($this->pickup_types);
            return true;
        }

        return false;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->getPickUpTypesArray();
        $this->cost = str_replace(',', '.', $this->cost);
    }

    private function getPickUpTypesArray()
    {
        if (!is_array($this->pickup_types) && $this->pickup_types !== null) {
            $this->pickup_types = json_decode($this->pickup_types, true);
            if (!is_array($this->pickup_types)) {
                $this->getPickUpTypesArray();
            }
        }

        return $this->pickup_types;
    }

    public function serialize()
    {
        $selfArray = $this->toArray();
        return json_encode($selfArray);
    }

    public function getPickup_address()
    {
        return '';
    }

    /**
     * @param bool $partial
     * @return $this
     */
    public function setPartial(bool $partial)
    {
        $this->partial = $partial;
        return $this;
    }

    /**
     * Частичная ли доставка
     * @return bool
     */
    public function isPartial(): bool {
        return (bool) $this->partial;
    }

    /**
     * Получение кода услуги по коду СД и типу услуги
     * @param string $carrierKey
     * @param string $serviceType
     * @return string|null
     */
    public function getServiceCode(string $carrierKey, string $serviceType): ?string
    {
        $service = Yii::$app->cache->getOrSet([$carrierKey, $serviceType], function () use($carrierKey, $serviceType) {
            return DeliveryService::find()
                ->joinWith(['delivery'])
                ->where(['delivery.carrier_key' => $carrierKey, 'type' => $serviceType])
                ->one();
        }, 3600);

        return $service ? $service->service_key : null;
    }

    /**
     * Доступные СД из списка по значению дополнительной услуги
     * @param string[] $carriers
     * @param string $type
     * @return array
     */
    public function getCarriersByService(
        array $carriers,
        string $type
    ): array {
        $deliveries = Yii::$app->cache->getOrSet([$carriers, $type], function () use($type) {
            return ArrayHelper::getColumn(
                DeliveryService::find()->joinWith(['delivery'])->where(['type' => $type])->asArray()->all(),
                'delivery.carrier_key'
            );
        }, 3600);
        $result = array_intersect($carriers, $deliveries);
        return empty($result) ? ['NULL'] : $result;
    }

    public function getDeliveryServiceNames(): string
    {
        if ($this->partial) {
            return '( '.Yii::t('delivery', DeliveryService::SERVICE_PARTIAL).' )';
        }
        return '';
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param array $orderDeliveries
     * @param string $filter
     * @return array
     */
    public function getFilteredDeliveries(array $orderDeliveries, string $filter = 'cheapest'): array
    {
        $filterIndexes = [];
        if ($filter === 'cheapest') {
            foreach ($orderDeliveries as $index => $delivery) {
                if ($delivery->type === OrderDelivery::DELIVERY_TO_DOOR) {
                    if (!isset($filterIndexes[$delivery->carrier_key])) {
                        $filterIndexes[$delivery->carrier_key] = ['cost' => $delivery->cost, 'index' => $index, 'max_term' => $delivery->max_term];
                    } elseif ($filterIndexes[$delivery->carrier_key]['cost'] > $delivery->cost) {
                        $filterIndexes[$delivery->carrier_key] = ['cost' => $delivery->cost, 'index' => $index, 'max_term' => $delivery->max_term];
                    } elseif ($filterIndexes[$delivery->carrier_key]['cost'] == $delivery->cost && $filterIndexes[$delivery->carrier_key]['max_term'] > $delivery->max_term) {
                        $filterIndexes[$delivery->carrier_key] = ['cost' => $delivery->cost, 'index' => $index, 'max_term' => $delivery->max_term];
                    }
                }
            }
        } elseif ($filter === 'fastest') {
            foreach ($orderDeliveries as $index => $delivery) {
                if ($delivery->type === OrderDelivery::DELIVERY_TO_DOOR) {

                    $fastTariffId = self::FASTEST_TARIFFS[$delivery->carrier_key] ?? 0;

                    if (!isset($filterIndexes[$delivery->carrier_key])) {
                        $filterIndexes[$delivery->carrier_key] = [
                            'cost' => $delivery->cost,
                            'index' => $index,
                            'max_term' => $delivery->max_term,
                            'tariff_id' => $delivery->tariff_id
                        ];
                    } elseif (
                        $fastTariffId == $delivery->tariff_id
                    ) {
                        $filterIndexes[$delivery->carrier_key] = [
                            'cost' => $delivery->cost,
                            'index' => $index,
                            'max_term' => $delivery->max_term,
                            'tariff_id' => $delivery->tariff_id
                        ];
                    } elseif (
                        $filterIndexes[$delivery->carrier_key]['max_term'] > $delivery->max_term
                        && $filterIndexes[$delivery->carrier_key]['tariff_id'] != $fastTariffId
                    ) {
                        $filterIndexes[$delivery->carrier_key] = [
                            'cost' => $delivery->cost,
                            'index' => $index,
                            'max_term' => $delivery->max_term,
                            'tariff_id' => $delivery->tariff_id
                        ];
                    } elseif (
                        $filterIndexes[$delivery->carrier_key]['max_term'] == $delivery->max_term
                        && $filterIndexes[$delivery->carrier_key]['cost'] > $delivery->cost
                        && $filterIndexes[$delivery->carrier_key]['tariff_id'] != $fastTariffId
                    ) {
                        $filterIndexes[$delivery->carrier_key] = [
                            'cost' => $delivery->cost,
                            'index' => $index,
                            'max_term' => $delivery->max_term,
                            'tariff_id' => $delivery->tariff_id
                        ];
                    }
                }
            }
        }

        foreach ($orderDeliveries as $index => $delivery) {
            if ($delivery->type === OrderDelivery::DELIVERY_TO_DOOR) {
                foreach ($filterIndexes as $carrierKey => $filterIndex) {
                    if ($carrierKey === $delivery->carrier_key && $filterIndex['index'] !== $index) {
                        unset($orderDeliveries[$index]);
                    }
                }
            }
        }

        if ($filter === 'cheapest') {
            ArrayHelper::multisort($orderDeliveries, 'cost');
        } elseif ($filter === 'fastest') {
            ArrayHelper::multisort($orderDeliveries, ['max_term', 'cost']);
        } else {
            ArrayHelper::multisort($orderDeliveries, ['cost', 'max_term']);
        }

        return $orderDeliveries;
    }

    /**
     * @param OrderDelivery[] $orderDeliveries
     * @param string $carrierKey
     * @param string $deliveryType
     * @return OrderDelivery|null
     */
    public function getChosenDelivery(
        array $orderDeliveries,
        string $carrierKey,
        string $deliveryType
    ): ?OrderDelivery {
        foreach ($orderDeliveries as $orderDelivery) {
            if ($orderDelivery->carrier_key == $carrierKey
                && $orderDelivery->type == $deliveryType
                && (!isset($delivery) || $delivery->cost > $orderDelivery->cost)
            ) {
                $delivery =  $orderDelivery;
            }
        }

        return $delivery ?? null;
    }

    /**
     * @param OrderDelivery[] $orderDeliveries
     * @param float $cost
     * @param string $deliveryType
     * @return OrderDelivery|null
     */
    public function getCheapestDelivery(
        array $orderDeliveries,
        float $cost,
        string $deliveryType
    ): ?OrderDelivery {
        foreach ($orderDeliveries as $orderDelivery) {
            if ($orderDelivery->type == $deliveryType
                && (!isset($delivery) || $delivery->cost > $orderDelivery->cost)
            ) {
                $delivery =  $orderDelivery;
            }
        }

        return (!empty($delivery) && $delivery->cost < $cost) ? $delivery : null;
    }
}
