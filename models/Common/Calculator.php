<?php
namespace app\models\Common;

use app\api\models\Calculator\Address;
use app\components\Clients\Dadata;
use app\delivery\DeliveryHelper;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Rate;
use app\models\Shop;
use app\models\ShopType;
use app\models\Tariff;
use app\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class Calculator extends Model
{
    /** @var string */
    public $city;
    /** @var string */
    public $city_fias_id;
    /** @var string */
    public $payment_method;
    /** @var integer */
    public $width;
    /** @var integer */
    public $cost;
    /** @var integer */
    public $accessed_cost;
    /** @var integer */
    public $height;
    /** @var integer */
    public $length;
    /** @var integer */
    public $weight;
    /** @var integer */
    public $shop_id;
    /** @var integer */
    public $warehouse_id;
    /** @var integer */
    public $product_count;
    /** @var boolean */
    public $partial;

    /** @var Address */
    private $toAddress;
    /** @var Address */
    private $fromAddress;
    /** @var array */
    private $allowedDeliveries = [];
    /** @var array */
    private $allowedTypes = [];

    /** @var Shop */
    private $shop;

    /** @var integer Сумма округления */
    private $roundingOff;
    /** @var integer Тип округления (в большую или меньшую сторону) */
    private $roundingOffPrefix;
    /** @var integer Количество дней обработки заказа */
    private $proccessDayCount;

    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            [['city', 'city_fias_id', 'payment_method'], 'string'],
            [['city', 'city_fias_id'], 'requiredAtLeastOne'],
            [['payment_method'], 'in', 'range' => [
                Order::PAYMENT_METHOD_FULL_PAY,
                Order::PAYMENT_METHOD_PRODUCT_PAY,
                Order::PAYMENT_METHOD_NO_PAY,
                Order::PAYMENT_METHOD_DELIVERY_PAY,
            ]],
            [['width', 'length', 'height'], 'default', 'value' => 10],
            [['weight', 'width', 'length', 'height', 'cost', 'accessed_cost'], 'number'],
            [['width', 'length', 'height', 'cost', 'accessed_cost'], 'compare', 'compareValue' => 0, 'operator' => '>=', 'type' => 'number'],
            [
                ['weight'],
                'compare',
                'compareValue' => 1,
                'operator' => '>=',
                'type' => 'number',
                'message' => Yii::t('calculator', 'Weight must be greater than 0.01 kg'),
            ],
            [['shop_id', 'warehouse_id', 'product_count'], 'integer'],
            [['partial'], 'boolean'],
            [['shop_id', 'cost', 'weight'], 'required']
        ];

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $shopIds      = $user->getAllowedShopIds();
            $warehouseIds = $user->getAllowedWarehouseIds();

            if ($shopIds !== []) {
                $rules[] = [['shop_id'], 'in', 'range' => ($shopIds === false ? [] : $shopIds)];
            }

            if ($warehouseIds !== []) {
                $rules[] = [['warehouse_id'], 'in', 'range' => ($warehouseIds === false ? [] : $warehouseIds)];
            }
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'city' => Yii::t('calculator', 'City'),
            'city_fias_id' => Yii::t('calculator', 'City Fias Id'),
            'payment_method' => Yii::t('calculator', 'Payment Method'),
            'weight' => Yii::t('calculator', 'Weight'),
            'length' => Yii::t('calculator', 'Length'),
            'height' => Yii::t('calculator', 'Height'),
            'cost' => Yii::t('calculator', 'Cost'),
            'accessed_cost' => Yii::t('calculator', 'Accessed cost'),
            'shop_id' => Yii::t('calculator', 'Shop ID'),
            'partial' => Yii::t('calculator', 'Partial'),
            'warehouse_id' => Yii::t('calculator', 'Warehouse ID'),
        ];
    }

    /**
     * @validator
     * @param $attribute_name
     * @param $params
     * @return bool
     */
    public function requiredAtLeastOne($attribute_name, $params): bool
    {
        if (empty($this->city) && empty($this->city_fias_id)) {
            $this->addError($attribute_name, Yii::t('calculator', 'At least 1 of the field must be filled up properly'));
            return false;
        }

        return true;
    }

    /**
     * Загрузим все необходимые данные для калькулятора
     * @return bool
     */
    public function prepareData(): bool
    {
        if ($this->shop_id) {
            $this->shop = Shop::findOne($this->shop_id);
            if ($this->shop) {
                $this->allowedDeliveries = $this->getAllowedDeliveries();
                $this->allowedTypes = $this->getAllowedTypes();
                $this->roundingOff =  $this->shop->rounding_off ?? 1;
                $this->roundingOffPrefix = $this->shop->rounding_off_prefix ?? 0;
                $this->proccessDayCount = $this->shop->process_day ?? 0;
            }
        }

        if ($this->shop_id && !empty($this->shop)) {
            $warehouse = $this->shop->getWarehouse($this->warehouse_id ?? 0);

            if ($warehouse) {
                $this->warehouse_id = $warehouse->id;
                $this->fromAddress = (new Address())
                    ->setCity($warehouse->address->city)
                    ->setCityFiasId($warehouse->address->city_fias_id)
                    ->setAddressString($warehouse->address->full_address);
            }
        }

        if ($this->city || $this->city_fias_id) {
            $this->toAddress = new Address();
            $this->city && $this->toAddress->setCity($this->city);
            if (!$this->city_fias_id && $this->city) {
                $city = (new Dadata())->getCity($this->city);
                !empty($city) && $this->toAddress->setCityFiasId($city['data']['fias_id']);
            } else {
                $this->toAddress->setCityFiasId($this->city_fias_id);
            }
        }

        if (!$this->product_count) {
            $this->product_count = 1;
        }

        if ($this->accessed_cost === null) {
            $this->accessed_cost = $this->cost;
        }

        if (!$this->payment_method) {
            $this->payment_method = Order::PAYMENT_METHOD_FULL_PAY;
        }

        if ($this->payment_method == Order::PAYMENT_METHOD_NO_PAY) {
            $this->cost = 0;
        }

        $this->partial = (boolean) $this->partial;

        return true;
    }

    /**
     * Получение доступных СД для магазина
     * @return string[]
     */
    public function getAllowedDeliveries(): array
    {
        if (empty($this->allowedDeliveries)) {
            $result = [];

            if (!$this->shop) {
                return $result;
            }

            $carriers = Yii::$app->cache->getOrSet('shopDeliveries_' . $this->shop_id, function ()
            {
                return ArrayHelper::getColumn(
                    $this->shop->getDeliveries()
                        ->select('carrier_key')
                        ->asArray()
                        ->all(),
                    'carrier_key'
                );
            });

            // Возможно что СД не подходит по весу, необходимо их отсеить.
            foreach ($carriers as $carrier) {
                if ($this->weight <= DeliveryHelper::$deliveryWeightLimits[$carrier]) {
                    $result[] = $carrier;
                }
            }
            $this->allowedDeliveries = empty($result) ? ["NULL"] : $result;
        }
        return $this->allowedDeliveries;
    }

    /**
     * Получение доступных типов доставки для магазина
     * @return array
     */
    public function getAllowedTypes(): array
    {
        if (empty($this->allowedTypes)) {
            $deliveryTypes = Yii::$app->cache->getOrSet('shopTypes_' . $this->shop_id, function ()
            {
                $result = [];
                $types = ArrayHelper::getColumn(
                    ShopType::find()
                        ->where(['shop_id' => $this->shop_id])
                        ->asArray()
                        ->all(),
                    'type'
                );
                foreach ((new OrderDelivery())->getDeliveryTypes() as $key => $type) {
                    $result[$key] = in_array($key, $types) ? 1 : 0;
                }
                return $result;
            }, 3600);

            $this->allowedTypes = !empty($deliveryTypes) ? $deliveryTypes : ['courier' => 1, 'mail' => 1, 'point' => 1];
        }
        return $this->allowedTypes;
    }

    /**
     * @return array
     */
    public function getOwnCouriers(): array
    {
        return Yii::$app->cache->getOrSet('shopOwnCouriers_' . $this->shop_id, function ()
        {
            return Tariff::find()->where(['shop_id' => $this->shop_id])->asArray()->all();
        }, 86400 * 31);
    }

    /**
     * @return array
     */
    public function getOwnRates(): array
    {
        return Yii::$app->cache->getOrSet('shopOwnRates_' . $this->shop_id, function ()
        {
            return Rate::find()->where(['shop_id' => $this->shop_id])->asArray()->all();
        }, 86400 * 31);

    }


    /**
     * @return Address|null
     */
    public function getToAddress(): ?Address
    {
        return $this->toAddress;
    }

    /**
     * @return Address
     */
    public function getFromAddress(): Address
    {
        return $this->fromAddress;
    }

    /**
     * @return int
     */
    public function getRoundingOff(): int
    {
        return $this->roundingOff;
    }

    /**
     * @return int
     */
    public function getRoundingOffPrefix(): int
    {
        return $this->roundingOffPrefix;
    }

    /**
     * @return int
     */
    public function getProccessDayCount(): int
    {
        return $this->proccessDayCount;
    }

    /**
     * @return array
     */
    public function getCacheParameters(): array
    {
        $cacheParams = array_merge(
            $this->toArray(),
            [
                'deliveries' =>  $this->getAllowedDeliveries(),
                'types' => $this->getAllowedTypes(),
                'rates' => $this->getOwnRates(),
                'couriers' => $this->getOwnCouriers(),
                'rounding_off' => $this->getRoundingOff(),
                'rounding_off_prefix' => $this->getRoundingOffPrefix(),
                'proccess_day' => $this->getProccessDayCount()
            ]
        );

        // Убираем поле shop_id что бы разные магазины использовали один и тот же кеш
        unset($cacheParams['shop_id']);
        return $cacheParams;
    }
}