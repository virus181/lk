<?php

namespace app\models;

use app\behaviors\LogBehavior;
use app\delivery\DeliveryHelper;
use app\models\queries\ShopQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop}}".
 *
 * @property int $id
 * @property int $additional_id
 * @property int $tariff_id
 * @property int $default_warehouse_id
 * @property string $name
 * @property string $legal_entity
 * @property string $phone
 * @property string $url
 * @property int $status
 * @property int $process_day
 * @property boolean $parse_address
 * @property int $rounding_off_prefix
 * @property int $rounding_off
 * @property array $statuses
 * @property int $created_at
 * @property int $updated_at
 * @property int $inn
 * @property int $kpp
 * @property boolean $fulfillment
 *
 * @property User[] $users
 * @property User[] $managers
 * @property Product[] $products
 * @property ShopDelivery[] $deliveries
 * @property array $types
 * @property Order[] $orders
 * @property Warehouse $defaultWarehouse
 * @property ShopTariff $tariff
 * @property ShopOption $option
 * @property ShopPhone[] $phones
 */
class Shop extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const TYPE_FULFILLMENT = 1;
    const TYPE_NO_FULFILLMENT = 0;

    const ROUNDING_OFF = 0;
    const ROUNDING_UP = 1;
    const ROUNDING_DOWN = -1;

    const ROUND_DECIMAL = 10;
    const ROUND_CENTURY = 100;

    const SCENARIO_MANUAL = 'MANUAL';
    const DEFAULT_PROCCESS_DAY_COUNT = 1;
    const DEFAULT_WAREHOUSE_ID = 2;

    const TEST_IDS = [1, 226, 254, 289, 298];

    /** @var int[] */
    public $warehouseIds = [];
    /** @var int[] */
    private $managerIds = [];
    private $deliveries = [];

    /** @var array */
    public $types;
    public $tariffName;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop}}';
    }

    /**
     * @inheritdoc
     * @return ShopQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array(
            TimestampBehavior::className(),
            LogBehavior::className(),
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [
                'phone',
                'filter',
                'filter' => function () {
                    return '+7' . $this->getClearPhone();
                },
                'on' => self::SCENARIO_MANUAL
            ],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['status', 'additional_id', 'default_warehouse_id', 'process_day'], 'integer'],
            [['name', 'default_warehouse_id'], 'required'],
            [
                'phone',
                'string',
                'notEqual' => Yii::t('app', 'Значение «Телефон» должно содержать 11 цифр и начинаться с 7, +7, 8 или 9'),
                'length' => 12,
                'on' => self::SCENARIO_MANUAL
            ],
            [['name'], 'string', 'max' => 512],
            [['url', 'legal_entity'], 'string', 'max' => 255],
            [['inn'], 'string', 'min' => 10, 'max' => 12],
            [['process_day'], 'default', 'value' => 1],
            [['process_day'], 'number', 'min' => 0, 'max' => 99],
            [['kpp'], 'string', 'length' => 9],
            [['inn'], 'unique', 'targetClass' => Shop::className(), 'message' => Yii::t('app', 'This inn has already been taken')],
            [['kpp'], 'unique', 'targetClass' => Shop::className(), 'message' => Yii::t('app', 'This kpp has already been taken')],
            [['fulfillment', 'parse_address'], 'boolean'],
            [['rounding_off_prefix', 'rounding_off', 'tariff_id'], 'number'],
            [['rounding_off'], 'default', 'value' => '10'],
            [['warehouseIds'], 'default', 'value' => []],
            [['deliveries', 'types'], 'default', 'value' => []],
            [['deliveries', 'types', 'phones'], 'safe'],
            [['name', 'url', 'inn', 'kpp', 'legal_entity'], 'trim']
        ];

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $warehouseIds = $user->getAllowedWarehouseIds();

            if ($warehouseIds !== []) {
                $rules[] = ['warehouseIds', 'each', 'rule' => ['in', 'range' => $warehouseIds]];
            }
        }

        return $rules;
    }

    public function getClearPhone()
    {
        $phone = preg_replace('/\W|_/', "", $this->phone);

        $first = substr($phone, 0, 1);

        if ($first == 8 || $first == 7) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    /**
     * @param array $phones
     */
    public function setPhones(array $phones) {
        $this->phones = $phones;
    }

    /**
     * @param ShopTariff $tariff
     */
    public function setTariff(ShopTariff $tariff) {
        $this->tariff = $tariff;
    }

    /**
     * @param ShopOption $option
     */
    public function setOption(ShopOption $option) {
        $this->option = $option;
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

        $fields['status'] = function () {
            $statuses = $this->getStatuses();
            return mb_convert_case($statuses[$this->status], MB_CASE_TITLE, "UTF-8");
        };

        $fields['fulfillment'] = function () {
            return (bool)$this->fulfillment;
        };

        unset($fields['additional_id']);

        if (Yii::$app->params['environment'] == 'api') {

            $fields['phone'] = function () {
                return (new \app\models\Helper\Phone($this->phone))->getHumanView();
            };

            unset($fields['created_at']);
            unset($fields['updated_at']);
            unset($fields['tariff_id']);
            unset($fields['default_warehouse_id']);
            unset($fields['fulfillment']);
            unset($fields['process_day']);
            unset($fields['parse_address']);
            unset($fields['rounding_off']);
            unset($fields['rounding_off_prefix']);
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return $statuses = [
            self::STATUS_ACTIVE => Yii::t('app', 'active'),
            self::STATUS_DELETED => Yii::t('app', 'deleted'),
        ];
    }

    /**
     * @return array
     */
    public static function getStatusList()
    {
        return $statuses = [
            self::STATUS_ACTIVE => Yii::t('app', 'active'),
            self::STATUS_DELETED => Yii::t('app', 'deleted'),
        ];
    }

    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields[] = 'users';
        $fields[] = 'products';
        $fields[] = 'orders';

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'additional_id' => Yii::t('app', 'Additional Sklad Shop ID'),
            'name' => Yii::t('app', 'Site name'),
            'legal_entity' => Yii::t('app', 'Legal entity'),
            'url' => Yii::t('app', 'Url'),
            'fulfillment' => Yii::t('app', 'Fulfillment'),
            'status' => Yii::t('app', 'Status'),
            'default_warehouse_id' => Yii::t('app', 'Default Warehouse'),
            'warehouseIds' => Yii::t('app', 'Warehouse Ids'),
            'process_day' => Yii::t('app', 'Process Day'),
            'phone' => Yii::t('app', 'Contact Phone'),
            'inn' => Yii::t('app', 'INN'),
            'managerIds' => Yii::t('shop', 'Managers'),
            'kpp' => Yii::t('app', 'KPP'),
            'deliveries' => Yii::t('app', 'Carriers'),
            'tariff_id' => Yii::t('shop', 'Tariff ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('{{%user_shop}}', ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('{{%shop_manager}}', ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveries()
    {
        return $this->hasMany(Delivery::className(), ['id' => 'delivery_id'])->viaTable('{{%shop_delivery}}', ['shop_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypes()
    {
        return $this->hasMany(ShopType::className(), ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhones()
    {
        return $this->hasMany(ShopPhone::className(), ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultWarehouse()
    {
        return $this->hasOne(Warehouse::className(), ['id' => 'default_warehouse_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(ShopTariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOption()
    {
        return $this->hasOne(ShopOption::className(), ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouses()
    {
        return $this->hasMany(Warehouse::className(), ['id' => 'warehouse_id'])->viaTable('{{%shop_warehouse}}', ['shop_id' => 'id']);
    }

    /**
     * @param null $statusCode
     * @return string
     */
    public function getStatusName($statusCode = null)
    {
        return ArrayHelper::getValue($this->statuses, $statusCode ? $statusCode : $this->status);
    }

    /**
     * @return bool
     */
    public function isFulfillment()
    {
        return (is_null($this->fulfillment) || $this->fulfillment) ? true : false;
    }

    /**
     * @return array
     */
    public function getWarehouseIds(): array
    {
        if (!$this->warehouseIds) {
            $this->warehouseIds = ArrayHelper::getColumn(
                $this->getWarehouses()->select('id')->asArray()->all(),
                'id'
            );
        }
        return $this->warehouseIds ?? [];
    }

    /**
     * Получение склада, либо склад по умолчанию либо первый из привязанных
     *
     * @param int $id
     * @return Warehouse|null
     */
    public function getWarehouse(int $id = 0): ?Warehouse
    {
        // Если передакли конкретный ID попробуйем найти нужный Склад
        if ($id) {
            if ($this->default_warehouse_id == $id) {
                return $this->defaultWarehouse;
            }

            if (in_array($id, $this->getWarehouseIds())) {
                return Warehouse::findOne($id);
            }
        }

        // Если есть склад по умолчанию берем его
        if ($this->default_warehouse_id) {
            return $this->defaultWarehouse;
        }

        // Иначе берем первый из приявзанных складов
        $warehouseIds = $this->getWarehouseIds();
        if (!empty($warehouseIds)) {
            return Warehouse::findOne($warehouseIds[0]);
        }

        return null;
    }

    /**
     * @param array $warehouseIds
     */
    public function setWarehouseIds($warehouseIds)
    {
        $this->warehouseIds = is_array($warehouseIds) ? $warehouseIds : [$warehouseIds];
    }

    /**
     * Получение ID всех менеджеров магазина
     * @return array
     */
    public function getManagerIds(): array
    {
        if (!$this->managerIds) {
            $this->managerIds = ArrayHelper::getColumn(
                $this->getManagers()->select('id')->asArray()->all(),
                'id'
            );
        }
        return $this->managerIds;
    }

    /**
     * @param int[] $managerIds
     */
    public function setManagerIds(array $managerIds)
    {
        $this->managerIds = $managerIds;
    }

    public function setDeliveries($deliveryIds)
    {
        $this->deliveries = $deliveryIds;
    }

    /**
     * Получение доступных доставок для магазина
     *
     * @param int $weight
     * @return array|bool
     */
    public function getAllowedDeliveries($weight = 10)
    {
        $carriers = ArrayHelper::getColumn(
            $this->getDeliveries()
                ->select('carrier_key')
                ->asArray()
                ->all(),
            'carrier_key'
        );

        // Возможно что СД не подходит по весу, необходимо их отсеить.
        $result = [];
        foreach ($carriers as $carrier) {
            if ($weight <= DeliveryHelper::$deliveryWeightLimits[$carrier]) {
                $result[] = $carrier;
            }
        }
        return empty($result) ? ["NULL"] : $result;
    }

    /**
     * Выполняем действия после сохранения модели
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        foreach ($this->getUsers()->select('id')->all() as $user) {
            Yii::$app->cache->delete('allowedWarehouseForUser' . $user->id);
        }

        Yii::$app->cache->delete('shopTypes_' . $this->id);
        Yii::$app->cache->delete('shopTariff_' . $this->id);
        Yii::$app->cache->delete('shopOwnCouriers_' . $this->id);
        Yii::$app->cache->delete('shopOwnRates_' . $this->id);

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    /**
     * Массив опций округления стоимости доставки.
     *
     * @return array
     */
    public static function getRoundingItems()
    {
        return [
            ['label' => Yii::t('app', 'No rounding'), 'value' => self::ROUNDING_OFF],
            ['label' => Yii::t('app', 'Rounding up'), 'value' => self::ROUNDING_UP],
            ['label' => Yii::t('app', 'Rounding down'), 'value' => self::ROUNDING_DOWN],
        ];
    }

    /**
     * Массив опций значений округления цены.
     *
     * @return array
     */
    public static function getRoundingItemValues()
    {
        return [
            ['label' => Yii::t('app', 'Rounding decimal'), 'value' => self::ROUND_DECIMAL],
            ['label' => Yii::t('app', 'Rounding century'), 'value' => self::ROUND_CENTURY],
        ];
    }

    /**
     * Массив опций типов доставок.
     * 
     * @return array
     */
    public static function getDeliveryTypeItems()
    {
        $result = [];
        foreach ((new OrderDelivery())->getDeliveryTypes() as $key => $type) {
            $result[] = ['label' => $type, 'value' => $key];
        }
        return $result;
    }

    /**
     * Сеттер для типов доставки
     * @param array $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * Получаем типы доставок для магазина
     * @return array
     */
    public function getShopTypes(): array
    {
        return Yii::$app->cache->getOrSet('shopTypes_' . $this->id, function ()
        {
            $result = [];
            $types = ArrayHelper::getColumn(ShopType::find()->where(['shop_id' => $this->id])->asArray()->all(), 'type');
            foreach ((new OrderDelivery())->getDeliveryTypes() as $key => $type) {
                $result[$key] = in_array($key, $types) ? 1 : 0;
            }
            return $result;
        }, 3600);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return (string) $this->url;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->getStatuses()[$this->status];
    }

    /**
     * @return string
     */
    public function getDefaultWarehouseName(): string
    {
        return ($this->defaultWarehouse) ? $this->defaultWarehouse->name : '';
    }

    /**
     * @return array
     */
    public function getTariffNames(): array
    {
        foreach (ShopTariff::TARIFF_TYPES as $tariff) {
            $result[$tariff] = Yii::t('shop', $tariff);
        }

        return $result ?? [];
    }

    /**
     * @return array
     */
    public function getWorkSchemes(): array
    {
        foreach (ShopTariff::TARIFF_TYPES as $tariff) {
            $result[] = Yii::t('shop', sprintf('Work scheme for %s tariff', $tariff));
        }

        return $result ?? [];
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getShopCallUrl(Order $order): array
    {
        if (!$order->phone) {
            return [];
        }

        $params[] = '+7' . (new \app\models\Helper\Phone($order->phone))->getClearPhone();

        if (empty($this->phones)) {
            return [];
        }

        $params[] = '+7' . (new \app\models\Helper\Phone($this->phones[0]->phone))->getClearPhone();

        $params[] = '*' . str_pad(Yii::$app->user->getId(), 7, "0", STR_PAD_LEFT);

        if ($order->id) {
            $params[] = $order->id;
        }

        return [
            'url' => sprintf('sip:%s', implode('##', $params))
        ];
    }
}
