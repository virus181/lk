<?php

namespace app\models;

use app\behaviors\LogBehavior;
use app\behaviors\ProductSaver;
use app\behaviors\RelationSaveBehavior;
use app\components\SkladColletingCreater;
use app\delivery\Deliveries;
use app\delivery\DeliveryHelper;
use app\models\Common\Calculator;
use app\models\queries\OrderQuery;
use app\models\Repository\RegistryOrder;
use app\models\search\OrderSearch;
use app\models\sklad\Order as skladOrder;
use app\workflow\OrderWorkflow;
use app\workflow\WorkflowHelper;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\events\WorkflowEvent;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\httpclient\Message;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property int              $id
 * @property string           $status
 * @property string           $delivery_status
 * @property int              $shop_id
 * @property int              $warehouse_id
 * @property int              $address_id
 * @property int              $courier_id
 * @property int              $label_url
 * @property string           $provider_number
 * @property string           $dispatch_number
 * @property string           $shop_order_number
 * @property string           $fio
 * @property string           $email
 * @property string           $phone
 * @property string           $comment
 * @property float            $weight
 * @property float            $cost
 * @property float            $assessed_cost
 * @property string           $payment_method
 * @property int              $width
 * @property int              $length
 * @property int              $height
 * @property boolean          $is_api
 * @property boolean          $is_archive
 * @property int              $created_at
 * @property int              $updated_at
 *
 * @property DeliveryStatus[] $deliveryStatuses
 * @property OrderProduct[]   $orderProducts
 * @property Product[]        $products
 * @property OrderStatus[]    $orderStatuses
 * @property OrderStatus      $orderStatus
 * @property Shop             $shop
 * @property Warehouse        $warehouse
 * @property OrderDelivery    $delivery
 * @property Address          $address
 * @property Courier          $courier
 * @property Message[]        $messages
 * @property Call[]           $orderCalls
 * @property RegistryOrder[]  $registryOrders
 *
 * @method bool sendToStatus(string $status)
 * @method string getDefaultWorkflowId
 */
class Order extends ActiveRecord
{
    const SCENARIO_CALCULATE = 'calculate';
    const SCENARIO_CALCULATE_API = 'calculate_api';
    const SCENARIO_CREATE_FROM_API = 'create_form_api';
    const SCENARIO_VIEW_FROM_API = 'view_form_api';

    const STATUS_DELIVERY_ERROR = 'deliveryError';
    const STATUS_CREATED = 'created';
    const STATUS_PRESALE = 'presale';
    const STATUS_IN_COLLECTING = 'inCollecting';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_READY_FOR_DELIVERY = 'redyForDelivery';
    const STATUS_WAITING_COURIER = 'waitingCourier';
    const STATUS_IN_DELIVERY = 'inDelivery';
    const STATUS_PARTIALLY_DELIVERED = 'partiallyDelivered';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELED_AT_DELIVERY = 'canceledAtDelivery';
    const STATUS_CANCELED = 'canceled';
    const STATUS_READY_DELIVERY = 'readyDelivery';
    const STATUS_ON_RETURN = 'onReturn';
    const STATUS_RETURNED = 'returned';

    const WORKFLOW_KEY = 'OrderWorkflow/';

    const STATUS_FINISHED = [
        self::WORKFLOW_KEY . self::STATUS_DELIVERED,
        self::WORKFLOW_KEY . self::STATUS_RETURNED,
        self::WORKFLOW_KEY . self::STATUS_CANCELED_AT_DELIVERY,
        self::WORKFLOW_KEY . self::STATUS_CANCELED
    ];

    const STATUS_NOT_DELIVEERY = [
        self::WORKFLOW_KEY . self::STATUS_CREATED,
        self::WORKFLOW_KEY . self::STATUS_CONFIRMED,
        self::WORKFLOW_KEY . self::STATUS_IN_COLLECTING,
        self::WORKFLOW_KEY . self::STATUS_PRESALE,
        self::WORKFLOW_KEY . self::STATUS_CANCELED
    ];

    const STATUS_DELIVERING = [
        self::WORKFLOW_KEY . self::STATUS_IN_DELIVERY,
        self::WORKFLOW_KEY . self::STATUS_WAITING_COURIER,
        self::WORKFLOW_KEY . self::STATUS_READY_FOR_DELIVERY,
        self::WORKFLOW_KEY . self::STATUS_ON_RETURN,
        self::WORKFLOW_KEY . self::STATUS_READY_DELIVERY
    ];

    const STATUS_CANCELABLE = [
        self::WORKFLOW_KEY . self::STATUS_CREATED,
        self::WORKFLOW_KEY . self::STATUS_PRESALE,
        self::WORKFLOW_KEY . self::STATUS_IN_COLLECTING,
        self::WORKFLOW_KEY . self::STATUS_CONFIRMED,
        self::WORKFLOW_KEY . self::STATUS_READY_FOR_DELIVERY,
        self::WORKFLOW_KEY . self::STATUS_DELIVERY_ERROR,
    ];

    const STATUS_CONVERTABLE = [
        self::WORKFLOW_KEY . self::STATUS_PRESALE,
        self::WORKFLOW_KEY . self::STATUS_CONFIRMED,
        self::WORKFLOW_KEY . self::STATUS_IN_COLLECTING,
        self::WORKFLOW_KEY . self::STATUS_READY_FOR_DELIVERY,
    ];

    const STATUS_EDITABLE = [
        self::WORKFLOW_KEY . self::STATUS_CREATED,
        self::WORKFLOW_KEY . self::STATUS_PRESALE
    ];

    const STATUS_PAYMENT_CHANGABLE = [
        self::WORKFLOW_KEY . self::STATUS_CREATED,
        self::WORKFLOW_KEY . self::STATUS_IN_COLLECTING,
        self::WORKFLOW_KEY . self::STATUS_CONFIRMED,
        self::WORKFLOW_KEY . self::STATUS_PRESALE
    ];

    // Статусы в которых возможно перевести заказ в архивный
    const STATUS_ARCHIVABLE = [
        self::WORKFLOW_KEY . self::STATUS_CREATED,
        self::WORKFLOW_KEY . self::STATUS_PRESALE,
        self::WORKFLOW_KEY . self::STATUS_IN_COLLECTING,
        self::WORKFLOW_KEY . self::STATUS_CONFIRMED,
        self::WORKFLOW_KEY . self::STATUS_READY_FOR_DELIVERY,
        self::WORKFLOW_KEY . self::STATUS_DELIVERED,
        self::WORKFLOW_KEY . self::STATUS_RETURNED,
        self::WORKFLOW_KEY . self::STATUS_CANCELED_AT_DELIVERY,
        self::WORKFLOW_KEY . self::STATUS_CANCELED,
        self::WORKFLOW_KEY . self::STATUS_DELIVERY_ERROR
    ];

    const PAYMENT_METHOD_FULL_PAY = 'fullPay';
    const PAYMENT_METHOD_NO_PAY = 'noPay';
    const PAYMENT_METHOD_DELIVERY_PAY = 'deliveryPay';
    const PAYMENT_METHOD_PRODUCT_PAY = 'productPay';
    const PAYMENT_METHOD_COSTOM_PAY = 'customPay';

    const DEPRECATED_SYMBOLS = ['.', ' '];

    const PRICE_ATTRIBUTES = [
        'codCost',
        'cost'
    ];

    public $disabledEdit = false;
    public $disabledProductEdit = false;

    public $paymentMethod;
    public $city;
    public $city_fias_id;
    public $statusName;
    public $partial = false;
    public $address_detailed = false;

    protected $_codCost;
    private $_assessed_cost;
    private $_cost;
    private $_realDeliveryCost;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @inheritdoc
     * @return OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            LogBehavior::className(),
            [
                'class'     => RelationSaveBehavior::className(),
                'relations' => [
                    'products' => [
                        'value' => 'products',
                        'saver' => ProductSaver::className(),
                        'type'  => RelationSaveBehavior::MANY_MANY_TYPE,
                        'link'  => function (Order $order, Product $product) {
                            $orderProduct                 = new OrderProduct();
                            $orderProduct->order_id       = $order->id;
                            $orderProduct->product_id     = $product->id;
                            $orderProduct->quantity       = $product->quantity;
                            $orderProduct->weight         = $product->weight;
                            $orderProduct->width          = $product->width;
                            $orderProduct->length         = $product->length;
                            $orderProduct->height         = $product->height;
                            $orderProduct->price          = $product->price;
                            $orderProduct->accessed_price = $product->accessed_price;
                            $orderProduct->name           = $product->name;
                            return $orderProduct->save();
                        },
                    ],
                    'delivery' => [
                        'value' => 'delivery',
                        'type'  => RelationSaveBehavior::HAS_ONE_TYPE,
                    ],
                    'address'  => [
                        'value' => 'address',
                        'type'  => RelationSaveBehavior::HAS_ONE_TYPE,
                    ]
                ]
            ],
            [
                'class'             => SimpleWorkflowBehavior::className(),
                'defaultWorkflowId' => 'OrderWorkflow',
            ],
        ];
    }

    public function init()
    {
        $this->on(
            WorkflowEvent::beforeEnterStatus($this->getWorkflowStatusId(self::STATUS_DELIVERY_ERROR)),
            [$this, 'validateSendToErrorStatus']
        );

        if (ArrayHelper::getValue(Yii::$app->params, 'apiship.sendOrder')) {
            $this->on(
                WorkflowEvent::afterEnterStatus($this->getWorkflowStatusId(self::STATUS_READY_FOR_DELIVERY)),
                [$this, 'sendOrder']
            );
            $this->on(
                WorkflowEvent::afterEnterStatus($this->getWorkflowStatusId(self::STATUS_READY_FOR_DELIVERY)),
                [$this, 'getTrackNumber']
            );
            $this->on(
                WorkflowEvent::afterEnterStatus($this->getWorkflowStatusId(self::STATUS_READY_FOR_DELIVERY)),
                [$this, 'sendOrder']
            );
            $this->on(
                WorkflowEvent::afterEnterStatus($this->getWorkflowStatusId(self::STATUS_READY_FOR_DELIVERY)),
                [$this, 'getTrackNumber']
            );
        }

        if (ArrayHelper::getValue(Yii::$app->params, 'sklad.sendCollecting')) {
            $this->on(
                WorkflowEvent::beforeEnterStatus($this->getWorkflowStatusId(self::STATUS_IN_COLLECTING)),
                [$this, 'checkProductAvailability']
            );
            $this->on(
                WorkflowEvent::beforeEnterStatus($this->getWorkflowStatusId(self::STATUS_IN_COLLECTING)),
                [$this, 'sendCollecting']
            );
            $this->on(
                WorkflowEvent::beforeEnterStatus($this->getWorkflowStatusId(self::STATUS_CONFIRMED)),
                [$this, 'sendCollecting']
            );
        }

        $this->on(
            WorkflowEvent::afterEnterStatus($this->getWorkflowStatusId(self::STATUS_WAITING_COURIER)),
            [$this, 'sendEmailNotification']
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['phone'], 'filter', 'filter' => function () {
                return '+7' . $this->getClearPhone();
            }],
            [['fio', 'phone', 'shop_id', 'shop_order_number', '!status'], 'required'],
            [['warehouse_id'], 'default', 'value' => function () {
                return ($this->shop) ? $this->shop->default_warehouse_id : null;
            }],
            [['width', 'length', 'height'], 'default', 'value' => 10],
            [['shop_id'], 'validateShop', 'skipOnError' => true],
            [['cost', 'assessed_cost', 'weight'], 'required', 'on' => self::SCENARIO_CALCULATE],
            [['cost', 'assessed_cost', 'weight'], 'required', 'on' => self::SCENARIO_CALCULATE_API],
            [['weight'], 'integer'],
            [['cost', 'assessed_cost', 'width', 'length', 'height'], 'number'],
            [['label_url'], 'string'],
            [['payment_method'], 'in', 'range' => [
                self::PAYMENT_METHOD_FULL_PAY,
                self::PAYMENT_METHOD_NO_PAY,
                self::PAYMENT_METHOD_PRODUCT_PAY,
                self::PAYMENT_METHOD_DELIVERY_PAY
            ]],
            [['payment_method'], 'default', 'value' => self::PAYMENT_METHOD_FULL_PAY],
            [['!status'], 'string', 'max' => 128],
            [['dispatch_number'], 'string', 'max' => 256],
            [['created_at', 'updated_at'], 'integer'],
            [['shop_order_number', 'provider_number', 'fio'], 'string', 'max' => 255],
            [['phone'], 'string', 'notEqual' => Yii::t('app', 'Значение «Телефон» должно содержать 11 цифр и начинаться с 7, +7, 8 или 9'), 'length' => 12],
            [['email'], 'email'],
            [['comment'], 'string', 'max' => 512],
            [['shop_id'], 'exist', 'skipOnError' => false, 'targetClass' => Shop::className(), 'targetAttribute' => ['shop_id' => 'id']],
            [['warehouse_id'], 'exist', 'skipOnError' => false, 'targetClass' => Warehouse::className(), 'targetAttribute' => ['warehouse_id' => 'id']],
            [['shop_order_number'], 'unique', 'targetClass' => Order::className(), 'targetAttribute' => ['shop_order_number', 'shop_id'], 'message' => Yii::t('app', 'Shop order number must be unique for current shop')],
            [['disabledEdit'], 'default', 'value' => false],
            [['disabledEdit'], 'safe'],
            [['city_fias_id', 'partial'], 'safe', 'on' => self::SCENARIO_CALCULATE],
            [['city'], 'validateCity', 'on' => self::SCENARIO_CALCULATE],
            [['delivery_status', 'partial'], 'safe'],
            [['is_api', 'is_archive'], 'boolean'],
            [['shop_order_number'], 'validateShopNumber'],
            [['comment', 'email', 'fio'], 'trim']
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
     * Проверка даты доставки (Дата доставки не может быть меньше даты забора + минимальный срок доставки)
     *
     * @param $attribute
     */
    public function validateShopNumber($attribute)
    {
        if (!empty($this->shop_order_number)) {
            $hasError = false;
            foreach (self::DEPRECATED_SYMBOLS as $symbol) {
                if (strpos($this->shop_order_number, $symbol) !== false) {
                    $hasError = true;
                }
            }
            if ($hasError) {
                $this->addError($attribute, Yii::t('order', 'Shop order number contains illegal characters'));
            }

        }
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields               = parent::fields();
        $fields['created_at'] = function () {
            return date('Y-m-d H:i:s', $this->created_at);
        };

        $fields['updated_at'] = function () {
            return date('Y-m-d H:i:s', $this->updated_at);
        };

        if ($this->scenario == self::SCENARIO_VIEW_FROM_API) {

            $fields['status'] = function (Order $order) {
                return $order->getWorkflowStatusKey($order->status);
            };

            $fields['phone'] = function () {
                return (new \app\models\Helper\Phone($this->phone))->getHumanView();
            };

            $fields['width'] = function () {
                return (int) $this->width;
            };

            $fields['length'] = function () {
                return (int) $this->length;
            };

            $fields['height'] = function () {
                return (int) $this->height;
            };

            unset($fields['return_number']);
            unset($fields['provider_number']);
            unset($fields['address_id']);
            unset($fields['is_api']);
            unset($fields['is_archive']);
            unset($fields['label_url']);
            unset($fields['courier_id']);
            unset($fields['delivery_status']);
            unset($fields['comment']);
        }
        return $fields;
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        $fields             = parent::extraFields();
        $fields['delivery'] = function (Order $item) {
            $delivery = $item->delivery;
            unset($delivery->order_id);
            return $delivery;
        };

        $fields[]                 = 'products';
        $fields[]                 = 'warehouse';
        $fields[]                 = 'address';
        $fields[]                 = 'messages';
        $fields[]                 = 'shop';
        $fields['order_products'] = 'orderProducts';

        return $fields;
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios                                 = parent::scenarios();
        $scenarios[self::SCENARIO_CALCULATE]       = ['cost', 'weight', 'width', 'height', 'length', 'payment_method', 'city', 'city_fias_id', 'assessed_cost', 'disabledEdit', 'partial', 'warehouse_id', 'shop_id'];
        $scenarios[self::SCENARIO_CALCULATE_API]   = ['cost', 'payment_method', 'weight', 'width', 'height', 'length', 'city', 'city_fias_id', 'assessed_cost', 'warehouse_id', 'partial', 'shop_id'];
        $scenarios[self::SCENARIO_CREATE_FROM_API] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                     => Yii::t('app', 'Order ID'),
            'shop_id'                => Yii::t('app', 'Shop'),
            'warehouse_id'           => Yii::t('app', 'Warehouse'),
            'shop_order_number'      => Yii::t('app', 'Shop Number'),
            'fio'                    => Yii::t('app', 'Fio'),
            'email'                  => Yii::t('app', 'Email'),
            'phone'                  => Yii::t('app', 'Phone'),
            'city'                   => Yii::t('app', 'City'),
            'dispatch_number'        => Yii::t('app', 'Dispatch number'),
            'status'                 => Yii::t('app', 'Status'),
            'cost'                   => Yii::t('app', 'Cost'),
            'width'                  => Yii::t('app', 'Width'),
            'weight'                 => Yii::t('app', 'Weight'),
            'length'                 => Yii::t('app', 'Length'),
            'height'                 => Yii::t('app', 'Height'),
            'carrier_key'            => Yii::t('app', 'Carrier Key'),
            'address'                => Yii::t('app', 'Address'),
            'comment'                => Yii::t('app', 'Comment'),
            'payment_method'         => Yii::t('app', 'Payment method'),
            'created_at'             => Yii::t('app', 'Order Create Date'),
            'updated_at'             => Yii::t('app', 'Order Update Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCourier()
    {
        return $this->hasOne(Courier::className(), ['id' => 'courier_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderStatus()
    {
        return $this->hasOne(OrderStatus::className(), ['order_id' => 'id'])->orderBy(['created_at' => 'DESC']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryStatuses()
    {
        return $this->hasMany(DeliveryStatus::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['id' => 'product_id'])->viaTable('{{%order_product}}', ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderStatuses()
    {
        return $this->hasMany(OrderStatus::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderMessages()
    {
        return $this->hasMany(OrderMessage::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderCalls()
    {
        return $this->hasMany(Call::className(), ['order_id' => 'id']);
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
    public function getDelivery()
    {
        return $this->hasOne(OrderDelivery::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(Address::className(), ['id' => 'address_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegistryOrders()
    {
        return $this->hasMany(RegistryOrder::className(), ['order_id' => 'id']);
    }

    /**
     * @param $status
     * @return string
     */
    public function getWorkflowStatusId($status)
    {
        return $this->getDefaultWorkflowId() . '/' . $status;
    }

    /**
     * @param string $status
     * @return string
     */
    public function getWorkflowStatusName(string $status): string
    {
        $orderWorkflow = new OrderWorkflow();
        return (isset($orderWorkflow->getDefinition()['status'][$this->getWorkflowStatusKey($status)]))
            ? $orderWorkflow->getDefinition()['status'][$this->getWorkflowStatusKey($status)]['label']
            : 'New Order';
    }

    /**
     * @param string $status
     * @return string
     */
    public function getWorkflowStatusKey(string $status): string
    {
        return (count(explode('/', $status)) > 1) ? explode('/', $status)[1] : $status;
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if ($this->warehouse_id === null && $this->shop !== null) {
                $this->warehouse_id = $this->shop->default_warehouse_id;
            }

            return true;
        }

        return false;
    }

    public function getClearPhone($phone = '')
    {
        if (!$phone) {
            $phone = preg_replace('/\W|_/', "", $this->phone);
        } else {
            $phone = preg_replace('/\W|_/', "", $phone);
        }

        $first = substr($phone, 0, 1);

        if ($first == 8 || $first == 7) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Получение наложенного платежа
     *
     * @param int|null    $cost     Стоимость товаров
     * @param int|null    $delivery Стоимость доставки
     * @param string|null $payment  Тип оплаты
     * @return float|int|null
     * @internal param null $paymentMethod
     */
    public function getCodCost($cost = null, $delivery = null, $payment = null)
    {
        $productCost   = $cost ?? $this->getCost(false);
        $paymentMethod = $payment ?? $this->payment_method;

        if ($delivery) {
            $deliveryCost = $delivery;
        } else {
            $deliveryCost = $this->delivery ? (int)$this->delivery->cost : 0;
        }

        switch ($paymentMethod) {
            case self::PAYMENT_METHOD_NO_PAY:
                return 0;
                break;
            case self::PAYMENT_METHOD_PRODUCT_PAY:
                return $productCost;
                break;
            case self::PAYMENT_METHOD_DELIVERY_PAY:
                return $deliveryCost;
                break;
            default:
                return $productCost + $deliveryCost;
        }
    }

    /**
     * Получение наложенного платежа товаров
     *
     * @return float|int|null
     * @internal param null $paymentMethod
     */
    public function getProductCodCost()
    {
        switch ($this->payment_method) {
            case self::PAYMENT_METHOD_NO_PAY:
                return 0;
                break;
            case self::PAYMENT_METHOD_PRODUCT_PAY:
                return $this->getCost(false);
                break;
            case self::PAYMENT_METHOD_DELIVERY_PAY:
                return 0;
                break;
            default:
                return $this->getCost(false);
        }
    }

    /**
     * Получение стоимости доставки для покупателя
     *
     * @param bool $formatted
     * @return null
     */
    public function getCost($formatted = true)
    {
        if ($this->_cost === null && $this->orderProducts) {
            foreach ($this->orderProducts as $orderProducts) {
                $this->_cost += (float)$orderProducts->price * (int)$orderProducts->quantity;
            }
        }
        return $this->_cost;
    }

    /**
     * Получение стоимости доставки для покупателя
     *
     * @param bool $formatted
     * @return null
     */
    public function getOrderProductCost($formatted = true)
    {
        if ($this->_cost === null && $this->orderProducts) {
            foreach ($this->orderProducts as $orderProducts) {
                $this->_cost += (float)$orderProducts->price * (int)$orderProducts->quantity;
            }
        }

        return ($formatted && $this->_cost !== null)
            ? Yii::$app->formatter->asCurrency($this->_cost, 'RUB')
            : $this->_cost;
    }

    /**
     * Получение объема заказа в кубических сантиметрах
     *
     * @return int
     */
    public function getOrderProductsVolume(): int
    {
        $volume = 0;
        if ($this->orderProducts) {
            foreach ($this->orderProducts as $orderProducts) {
                $volume += ((int)$orderProducts->width * (int)$orderProducts->length * (int)$orderProducts->height);
            }
        }

        return $volume;
    }

    /**
     * Получение габаритов заказа
     *
     * @return null|array
     */
    public function getOrderDimensions(): ?array
    {
        if ($this->getOrderProductsVolume()) {
            $dimension = ceil(pow($this->getOrderProductsVolume(), 1 / 3));
            return [
                'width'  => $dimension,
                'length' => $dimension,
                'height' => $dimension
            ];
        }
        return null;
    }

    /**
     * @param bool $formatted
     * @return float|string|null
     */
    public function getDeliveryCost($formatted = true)
    {
        $deliveryCost = $this->delivery->cost ?? 0;
        $cost = (in_array($this->payment_method, [self::PAYMENT_METHOD_NO_PAY, self::PAYMENT_METHOD_PRODUCT_PAY])) ? 0 : $deliveryCost;
        return $formatted ? Yii::$app->formatter->asCurrency($cost, 'RUB') : $cost;
    }

    /**
     * Получение фактической стоимости доставки
     *
     * @return float
     */
    public function getRealDeliveryCost(): float
    {
        return $this->_realDeliveryCost ?? 0;
    }

    public function setCost($cost)
    {
        $this->_cost = $cost;
    }

    public function getProductsCount()
    {
        $count = 0;
        foreach ($this->orderProducts as $orderProduct) {
            $count += (int) $orderProduct->quantity;
        }

        return $count;
    }

    /**
     * @param string $phone
     * @return string
     */
    public function getNormalizePhone($phone = '')
    {
        $phone = $this->getClearPhone($phone);

        $phoneCode = substr($phone, 0, 3);

        $phoneEndFirst = substr($phone, 3, 3);
        $phoneEndMid   = substr($phone, 6, 2);
        $phoneEndLast  = substr($phone, 8, 2);

        return '+7' . ' (' . $phoneCode . ') ' . ' ' . $phoneEndFirst . '-' . $phoneEndMid . '-' . $phoneEndLast;
    }

    /**
     * @param $attribute
     */
    public function validateCity($attribute)
    {
        if ($this->city == null && $this->address === null) {
            $this->addError($attribute, Yii::t('app', '{attribute} cannot be blank.'));
        }
    }

    /**
     * @param $attribute
     */
    public function validateShop($attribute)
    {
        if ($this->warehouse === null && $this->warehouse_id === null) {
            $this->addError($attribute, Yii::t('app', 'This shop hase not default warehouse. Please set default warehouse settings for current shop.'));
        }

        if (!empty($this->shop) && !$this->shop->status) {
            $this->addError($attribute, Yii::t('app', 'Shop is blocked.'));
        }
    }

    /**
     * @return array
     */
    public function getPaymentMethods()
    {
        return [
            self::PAYMENT_METHOD_FULL_PAY     => Yii::t('app', 'Товары + доставка'),
            self::PAYMENT_METHOD_NO_PAY       => Yii::t('app', 'Оплата не требуется'),
            self::PAYMENT_METHOD_PRODUCT_PAY  => Yii::t('app', 'Оплата только за товар'),
            self::PAYMENT_METHOD_DELIVERY_PAY => Yii::t('app', 'Оплата только за доставку'),
        ];
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): ?string
    {
        return $this->getPaymentMethods()[$this->payment_method] ?? null;
    }

    /**
     * @param $orderProducts
     */
    public function setOrderProducts($orderProducts)
    {
        $this->orderProducts = $orderProducts;
    }

    /**
     * @param $products Product[]
     */
    public function setProducts($products)
    {
        $orderProducts = [];
        foreach ($products as $i => $product) {
            $orderProduct          = new OrderProduct();
            $orderProduct->product = $product;
            $orderProducts[$i]     = $orderProduct;
        }

        $this->orderProducts  = $orderProducts;
        $this->products       = $products;
        $this->weight        = null;
        $this->_cost          = null;
        $this->_assessed_cost = null;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        if (empty($this->weight) && $this->orderProducts) {
            foreach ($this->orderProducts as $orderProducts) {
                $this->weight += (int)$orderProducts->weight * (int)$orderProducts->quantity;
            }
        }
        return $this->weight ?? 10;
    }

    /**
     * @return float
     */
    public function getRealWeight(): float
    {
        // Будем получать по новому
        return 0;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Получение оценочной стоимости заказа
     *
     * @return null
     */
    public function getAssessed_cost()
    {
        if ($this->_assessed_cost === null && $this->orderProducts) {
            foreach ($this->orderProducts as $orderProducts) {
                if (!is_null($orderProducts->accessed_price)) {
                    $this->_assessed_cost += (float)$orderProducts->accessed_price * (int)$orderProducts->quantity;
                } else {
                    $this->_assessed_cost += (float)$orderProducts->price * (int)$orderProducts->quantity;
                }
            }
        }

        return $this->_assessed_cost;
    }

    public function setAssessed_cost($assessed_cost)
    {
        $this->_assessed_cost = $assessed_cost;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
        if ($this->delivery) {
            $this->delivery->city = $this->getRawCityFrom();
        }
    }

    /**
     * @return string
     */
    public function getRawCityFrom()
    {
        $cityParts = explode(' ', (($this->warehouse)
            ? $this->warehouse->address->city
            : $this->shop->defaultWarehouse->address->city));

        if ($cityParts) {
            unset($cityParts[0]);
            return implode(' ', $cityParts);
        }

        return '';
    }

    /**
     * @param WorkflowEvent $event
     * @return bool|\yii\httpclient\Response
     * @throws Exception
     */
    public function sendOrder(WorkflowEvent $event)
    {
        /** @var Order $order */
        $order = $event->sender->owner;
        $order = Order::findOne($order->id);

        if ($order->delivery !== null) {
            if ($order->provider_number == null) {
                /** @var Deliveries $deliveries */
                $deliveries = Yii::createObject(Deliveries::className(), [$order, null]);
                return $deliveries->createOrder();
            } else {
                // Обновим только в том случае если у заказа нет трек номера
                if ($order->dispatch_number !== null) {
                    return true;
                }
                // Так как заказ уже создан
                /** @var Deliveries $deliveries */
                $deliveries = Yii::createObject(Deliveries::className(), [$order, null]);
                return $deliveries->updateOrder() && $deliveries->reSendOrder();
            }
        } else {
            $event->handled = true;
            $event->isValid = false;
            throw new Exception('Для перехода в статус необходимо выбрать способ доставки');
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function reSendOrder(): bool
    {
        if ($this->delivery !== null) {
            if ($this->provider_number != null) {
                /** @var Deliveries $deliveries */
                $deliveries = Yii::createObject(Deliveries::className(), [$this, null]);
                return $deliveries->updateOrder();
            } else {
                // Так как заказ еще не создан
                return true;
            }
        }
    }

    /**
     * @param WorkflowEvent $event
     * @throws Exception
     */
    public function getLabel(WorkflowEvent $event)
    {
        sleep(2);
        /** @var Order $order */
        $order = $event->sender->owner;
        $order = Order::findOne($order->id);
        /** @var Deliveries $deliveries */
        $deliveries = Yii::createObject(Deliveries::className(), [[$order], null]);
        try {
            try {
                $deliveries->getLabels();
            } catch (Exception $e) {
                sleep(10);
                if (!$deliveries->getLabels()) {
                    $event->handled = true;
                    $event->isValid = false;
                    throw $e;
                }
            }
        } catch (Exception $e) {
            if ($order->sendToErrorStatus()) {
                (new Query())->createCommand()->update('{{%order}}', ['status' => $order->status], ['id' => $order->id])->execute();
            }
            $event->handled = true;
            $event->isValid = false;
            throw $e;
        }
    }

    /**
     * @param WorkflowEvent $event
     * @throws Exception
     */
    public function getTrackNumber(WorkflowEvent $event)
    {
        /** @var Order $order */
        $order = $event->sender->owner;
        $order = Order::findOne($order->id);

        if ($order->delivery->carrier_key == DeliveryHelper::CARRIER_CODE_OWN) {
            return;
        }

        sleep(2);
        /** @var Deliveries $deliveries */
        $deliveries = Yii::createObject(Deliveries::className(), [$order, null]);
        try {
            try {
                $status = $deliveries->getCurrentStatus();
                if (isset($status['orderInfo']['providerNumber']) && trim($status['orderInfo']['providerNumber']) != '') {
                    (new Query())
                        ->createCommand()
                        ->update('{{%order}}', ['dispatch_number' => $status['orderInfo']['providerNumber']], ['id' => $order->id])
                        ->execute();
                } else {
                    throw new Exception('Не удалось получить трек номер');
                }
            } catch (Exception $e) {
                sleep(10);
                $status = $deliveries->getCurrentStatus();
                if (isset($status['orderInfo']['providerNumber']) && trim($status['orderInfo']['providerNumber']) != '') {
                    (new Query())
                        ->createCommand()
                        ->update('{{%order}}', ['dispatch_number' => $status['orderInfo']['providerNumber']], ['id' => $order->id])
                        ->execute();
                } else {
                    $event->handled = true;
                    $event->isValid = false;
                    throw $e;
                }
            }
        } catch (Exception $e) {
            if ($order->sendToErrorStatus()) {
                (new Query())->createCommand()->update('{{%order}}', ['status' => $order->status], ['id' => $order->id])->execute();
            }
            $event->handled = true;
            $event->isValid = false;
            throw $e;
        }
    }

    public function sendToErrorStatus()
    {
        $systemUser  = User::findOne(Yii::$app->params['systemUserId']);
        $currentUser = Yii::$app->user->identity;
        Yii::$app->user->switchIdentity($systemUser);
        if ($this->sendToStatus(self::STATUS_DELIVERY_ERROR)) {
            Yii::$app->user->switchIdentity($currentUser);
            return true;
        }

        Yii::$app->user->switchIdentity($currentUser);
        return false;
    }

    /**
     * Проверка доступности товаров при переводе в статус
     *
     * @param WorkflowEvent $event
     * @return bool
     * @throws \Exception
     */
    public function checkProductAvailability(WorkflowEvent $event)
    {
        /** @var Order $order */
        $order = $event->sender->owner;
        $order = Order::findOne($order->id);

        if ($order->shop->fulfillment || $order->shop->additional_id) {

            // Проверим существование заказа в CC
            if (skladOrder::findOne(['Name' => $order->shop_order_number, 'SNMarket' => $order->shop->additional_id])) {
                $event->isValid = true;
                $event->handled = false;
                return true;
            }

            foreach ($order->products as $product) {

                $oProduct = null;
                foreach ($order->orderProducts as $orderProduct) {
                    if ($orderProduct->product_id == $product->id) {
                        $oProduct = $orderProduct;
                    }
                }

                if ($product->count < $oProduct->quantity) {
                    $order->addError('products', Yii::t(
                        'app',
                        'Products in order is not enough: {name} (sku {barcode})',
                        [
                            'name'    => $product->name,
                            'barcode' => $product->barcode,
                        ]
                    ));
                    $event->isValid = false;
                    $event->handled = true;
                    throw new \Exception(
                        Yii::t('app', 'Products in order is not enough: {name} (sku {barcode})',
                            [
                                'name'    => $product->name,
                                'barcode' => $product->barcode,
                            ]),
                        ErrorException::ERROR_NOT_ENOUGH_PRODUCTS
                    );
                }
            }
        }
    }

    /**
     * @param WorkflowEvent $event
     * @throws \Exception
     */
    public function sendCollecting(WorkflowEvent $event)
    {
        /** @var Order $order */
        $order = $event->sender->owner;
        $order = Order::findOne($order->id);

        if ($order->delivery == null || $order->products == null) {
            $event->isValid = false;
            if ($order->delivery === null) {
                $order->addError('delivery', Yii::t('app', 'Delivery cannot be blank'));
            }
            if ($order->products === null || $order->products === []) {
                $order->addError('products', Yii::t('app', 'Products cannot be blank'));
            }
        }

        if ($event->isValid) {
            /** @var SkladColletingCreater $collectingCreater */
            $collectingCreater = Yii::createObject(SkladColletingCreater::className(), [$order]);
            $event->isValid    = $collectingCreater->createCollection();
        }

        if ($event->isValid === false) {
            $messages = [];
            foreach ($order->getErrors() as $attribute => $error) {
                if (is_array($error)) {
                    foreach ($error as $index => $errorName) {
                        $messages[] = $errorName;
                    }
                } else {
                    $messages[] = $error;
                }
            }

            throw new \Exception(implode('<br/>', $messages));
        }
    }

    /**
     * @return array
     */
    public function getStatuses(): array
    {
        $statuses = [
            self::STATUS_CREATED              => Yii::t('app', 'Created'),
            self::STATUS_PRESALE              => Yii::t('app', 'Presale'),
            self::STATUS_CANCELED             => Yii::t('app', 'Canceled'),
            self::STATUS_READY_FOR_DELIVERY   => Yii::t('app', 'Ready for delivery'),
            self::STATUS_WAITING_COURIER      => Yii::t('app', 'Waiting courrier'),
            self::STATUS_IN_DELIVERY          => Yii::t('app', 'In delivery'),
            self::STATUS_IN_COLLECTING        => Yii::t('app', 'In collecting'),
            self::STATUS_CANCELED_AT_DELIVERY => Yii::t('app', 'Canceled at delivery'),
            self::STATUS_CONFIRMED            => Yii::t('app', 'Confirmed'),
            self::STATUS_DELIVERED            => Yii::t('app', 'Delivered'),
            self::STATUS_PARTIALLY_DELIVERED  => Yii::t('app', 'Partially delivered'),
            self::STATUS_DELIVERY_ERROR       => Yii::t('app', 'Delivery Error'),
            self::STATUS_READY_DELIVERY       => Yii::t('app', 'Ready Delivery'),
            self::STATUS_ON_RETURN            => Yii::t('app', 'On Return'),
            self::STATUS_RETURNED             => Yii::t('app', 'Returned'),
        ];

        return $statuses;
    }

    /**
     * @return string
     */
    public function getShopOrderNumberForApiShip(): string
    {
        return $this->shop_id . '-' . $this->shop_order_number;
    }

    /**
     * @throws Exception
     */
    public function validateSendToErrorStatus()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if ($user->status !== User::STATUS_SYSTEM) {
            throw new Exception(Yii::t('app', 'You cannot translate to the error status'));
        }
    }

    /**
     * @param WorkflowEvent $event
     * @throws Exception
     * @deprecated
     */
    public function validateLeaveFromErrorStatus(WorkflowEvent $event)
    {
        /** @var User|IdentityInterface $user */
        $user = Yii::$app->user->identity;
        if (($user && $user->status !== User::STATUS_SYSTEM)) {
            if (self::isStatusEquals($event->getEndStatus()->getId(), self::STATUS_CANCELED) === false && !Yii::$app->user->can('/*')) {
                throw new Exception(Yii::t('app', 'You can not translate from an error status to the selected status'));
            }
        }
    }

    /**
     * @param $currentStatus
     * @param $status
     * @return bool|int
     */
    public static function isStatusEquals($currentStatus, $status)
    {
        return strpos((string)$currentStatus, (string)$status);
    }

    public function clearDeliveryData(WorkflowEvent $event)
    {
        /** @var Order $order */
        $order                  = $event->sender->owner;
        $order->provider_number = null;
        $order->label_url       = null;
    }

    /**
     * Выполняется после загрузки объекта
     */
    public function afterFind()
    {
        parent::afterFind();

        $status = json_decode($this->delivery_status, true);
        unset($status['createdProvider']);
        unset($status['providerCode']);
        unset($status['providerName']);
        unset($status['providerDescription']);

        $this->delivery_status = $status;
        $this->statusName      = $this->status ? $this->getWorkflowStatusName($this->status) : '';

        if ($this->delivery) {
            $this->partial = (bool)$this->delivery->partial;
        }

        foreach ($this->orderProducts as $orderProducts) {
            $this->_cost += (float)$orderProducts->price * (int)$orderProducts->quantity;
        }
    }

    /**
     * @param array $orderIds
     * @return array $orders
     */
    public function getCancelProperOrders($orderIds)
    {
        $privateUserStatuses = [
            $this->getWorkflowStatusId(Order::STATUS_CREATED),
            $this->getWorkflowStatusId(Order::STATUS_DELIVERY_ERROR),
            $this->getWorkflowStatusId(Order::STATUS_IN_COLLECTING),
            $this->getWorkflowStatusId(Order::STATUS_READY_FOR_DELIVERY)
        ];

        return Order::find()
            ->joinWith('shop')
            ->where(['AND',
                ['IN', 'order.id', $orderIds],
                ['OR',
                    ['AND',
                        'order.status = "' . $this->getWorkflowStatusId(Order::STATUS_CREATED) . '"',
                        ['OR',
                            'shop.fulfillment = 1',
                            'shop.fulfillment IS NULL'
                        ]
                    ],
                    ['AND',
                        'shop.fulfillment = 0',
                        ['IN', 'order.status', $privateUserStatuses]
                    ]
                ]
            ])
            ->all();
    }

    public function __clone()
    {
        $this->shop_order_number = sprintf('%d-clone %s', $this->id, date('H:i:s', time()));
        $this->status            = $this->getWorkflowStatusId(Order::STATUS_CREATED);
        $this->label_url         = null;
        $this->provider_number   = null;
        $this->dispatch_number   = null;
        $this->delivery_status   = null;
        $this->courier_id        = null;
        $this->created_at        = time();
        $this->statusName        = $this->getWorkflowStatusName($this->status);
        $this->id                = null;
        $this->isNewRecord       = true;
    }

    /**
     * @param Order $order
     * @return Order
     * @throws Exception
     */
    public function getCopiedOrder(Order $order): Order
    {
        $newOrder = new Order();

        $newOrder->shop_order_number = sprintf('%d-clone-%s', $this->id, date('H:i:s', time()));
        $newOrder->status            = $this->getWorkflowStatusId(Order::STATUS_CREATED);
        $newOrder->fio               = $order->fio;
        $newOrder->email             = $order->email;
        $newOrder->phone             = $order->phone;
        $newOrder->payment_method    = $order->payment_method;
        $newOrder->shop_id           = $order->shop_id;
        $newOrder->warehouse_id      = $order->warehouse_id;

        if ($newOrder->validate() && $newOrder->save()) {
            return $newOrder;
        }

        throw new Exception(implode('\n', $newOrder->getFirstErrors()));
    }

    /**
     * Сохранение копий продуктов
     *
     * @param OrderProduct $product
     * @throws Exception
     */
    public function cloneOrderProduct($product)
    {
        $result              = clone $product;
        $result->order_id    = $this->id;
        $result->isNewRecord = true;

        if ($result->validate()) {
            $result->save();
        } else {
            throw new Exception(sprintf('Product %d was not saved', $product->product_id));
        }
    }

    /**
     * Сохранение копии доставки
     *
     * @param OrderDelivery $delivery
     */
    public function cloneOrderDelivery($delivery)
    {
        $delivery->order_id    = $this->id;
        $delivery->id          = null;
        $delivery->isNewRecord = true;
        if ($delivery->validate()) {
            $delivery->save();
        }
    }

    /**
     * Получение стоимости заказа по номеру заказа
     *
     * @param $orderId
     * @return null
     */
    public static function getOrderCost($orderId)
    {
        $order = self::find()->where(['id' => $orderId])->one();
        return $order->getCost(false);
    }

    /**
     * Получение наложенного платежа по номеру заказа
     *
     * @param $orderId
     * @return float|int|null
     */
    public static function getOrderCodCost($orderId)
    {
        $order = self::find()->where(['id' => $orderId])->one();

        switch ($order->payment_method) {
            case self::PAYMENT_METHOD_NO_PAY:
                return 0;
                break;
            case self::PAYMENT_METHOD_PRODUCT_PAY:
                return $order->getCost(false);
                break;
            case self::PAYMENT_METHOD_DELIVERY_PAY:
                return $order->delivery ? $order->delivery->cost : 0;
                break;
            default:
                return $order->getCost(false) + ($order->delivery ? $order->delivery->cost : 0);
        }
    }

    /**
     * Получение массива возможных методов доставки для конкретной СД
     *
     * @param string $carrierKey
     * @return array
     */
    public static function getPaymentMethodsByCarrierKey(string $carrierKey): array
    {
        switch ($carrierKey) {
            case DeliveryHelper::CARRIER_CODE_B2CPL:
            case DeliveryHelper::CARRIER_CODE_BOXBERRY:
                return [
                    self::PAYMENT_METHOD_FULL_PAY    => Yii::t('app', 'Товары + доставка'),
                    self::PAYMENT_METHOD_NO_PAY      => Yii::t('app', 'Оплата не требуется'),
                    self::PAYMENT_METHOD_PRODUCT_PAY => Yii::t('app', 'Оплата только за товар')
                ];
                break;
            default:
                return (new Order())->getPaymentMethods();
        }
    }

    /**
     * Получение массива возможных методов доставки для конкретной СД
     *
     * @param string $serviceKey
     * @return array
     */
    public static function getPaymentMethodsByServiceKey(string $serviceKey): array
    {
        switch ($serviceKey) {
            case DeliveryHelper::SERVICE_KEY_PARTIAL:
                return [
                    self::PAYMENT_METHOD_FULL_PAY    => Yii::t('app', 'Товары + доставка'),
                    self::PAYMENT_METHOD_PRODUCT_PAY => Yii::t('app', 'Оплата только за товар')
                ];
                break;
            default:
                return (new Order())->getPaymentMethods();
        }
    }

    /**
     * @return null|string
     */
    public function getDeliveryTypeName(): ?string
    {
        if ($this->delivery) {
            return Helper::getDeliveryTypeName($this->delivery->type);
        }
        return null;
    }

    /**
     * @return string
     */
    public function getCreatedDate(): string
    {
        return date(OrderSearch::DATE_FORMAT, $this->created_at);
    }

    /**
     * @return string
     */
    public function getFIO(): string
    {
        return $this->fio;
    }

    /**
     * @param bool $formatted
     * @return string
     */
    public function getCOD($formatted = true): string
    {
        return $formatted ? Yii::$app->formatter->asCurrency($this->getCodCost(), 'RUB') : $this->getCodCost();
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->getNormalizePhone($this->phone);
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getShopOrderNumber(): string
    {
        return $this->shop_order_number ? $this->shop_order_number : $this->id;
    }

    /**
     * @return string
     */
    public function getCurrentStatusName(): string
    {
        return WorkflowHelper::getLabel($this);
    }

    /**
     * @return null|string
     */
    public function getAddressFull(): ?string
    {
        return $this->address ? $this->address->full_address : null;
    }

    /**
     * @return null|string
     */
    public function getDeliveryType(): ?string
    {
        return $this->delivery ? Helper::getDeliveryTypeName($this->delivery->type) : null;
    }

    /**
     * @return null|string
     */
    public function getDeliveryCarrierName(): ?string
    {
        return $this->delivery ? DeliveryHelper::getName($this->delivery->carrier_key) : null;
    }

    /**
     * @return null|string
     */
    public function getStatusCall(): ?string
    {
        return count($this->orderCalls) ? Html::tag(
            'div',
            Yii::t('app', 'Ok'),
            ['class' => 'label label-success']
        ) : '';
    }

    /**
     * @return null|string
     */
    public function getDispatchNumber(): ?string
    {
        return $this->dispatch_number ? $this->dispatch_number : null;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getShopName(): ?string
    {
        return $this->shop ? $this->shop->name : null;
    }

    /**
     * @return null|string
     */
    public function getPrintUrl(): ?string
    {
        return $this->dispatch_number
            ? Url::to(['label/pdf', 'id' => $this->id])
            : null;
    }

    /**
     * Отправка нотификации при переводе в статус Ожидает курьера
     */
    public function sendEmailNotification()
    {
        $event = new \app\events\Courier();
        if ($this->delivery->carrier_key == DeliveryHelper::CARRIER_CODE_OWN) {
            $rateId = $this->delivery->tariff_id;
            $rate   = Rate::find()->where(['id' => $rateId])->one();
            $event->setOrder($this);
            $event->setEmail($rate->notify_email);
            Yii::$app->trigger(\app\events\Courier::EVENT_CALL_COURIER, $event);
        }
    }

    public function setApiViewScenario()
    {
        // Тут непотребство, нужно как то убирать такую штуку, возможно нужно добавить environment
        $this->scenario                     = self::SCENARIO_VIEW_FROM_API;
        $this->warehouse->scenario          = self::SCENARIO_VIEW_FROM_API;
        $this->shop->scenario               = self::SCENARIO_VIEW_FROM_API;
        $this->warehouse->address->scenario = self::SCENARIO_VIEW_FROM_API;
        $this->address->scenario            = self::SCENARIO_VIEW_FROM_API;
        foreach ($this->orderProducts as $orderProduct) {
            $orderProduct->scenario = self::SCENARIO_VIEW_FROM_API;
        }
    }

    /**
     * @return array
     */
    public function getStatusButtons(): array
    {
        foreach (WorkflowHelper::getNextTransitionListData($this) as $status => $transition) {

            $isAllowForOwnOrder = ArrayHelper::getValue($transition, 'isAllowForOwnOrder');
            $isSystem           = ArrayHelper::getValue($transition, 'isSystem');

            if (($status === self::STATUS_DELIVERY_ERROR || $status === self::STATUS_WAITING_COURIER)
                && !($isAllowForOwnOrder
                    && $this->delivery
                    && $this->delivery->carrier_key == \app\delivery\DeliveryHelper::CARRIER_CODE_OWN)
            ) {
                continue;
            }

            $type = ArrayHelper::getValue($transition, 'type');
            if (!is_null($type) && $type != (int)$this->shop->fulfillment) {
                continue;
            }

            if ($isSystem
                && !($isAllowForOwnOrder
                    && $this->delivery->carrier_key == \app\delivery\DeliveryHelper::CARRIER_CODE_OWN)
            ) {
                continue;
            }

            $options = ArrayHelper::getValue($transition, 'options', []);

            if (isset($options['data-action'])) {
                $options['data-toggle'] = "modal";
                $options['data-href']   = Url::to(['order/' . $options['data-action'], 'id' => $this->id]);
                $options['data-target'] = "#modal";
            } else {
                $options['data-method'] = 'post';
                $options['href']        = Url::to(['order/set-status', 'id' => $this->id, 'status' => $status]);
            }

            if (!in_array($this->status, self::STATUS_CANCELABLE)
                && $status == self::STATUS_CANCELED
            ) {
                $options['class'] .= ' is-not-manual';
                unset($options['href']);
                unset($options['data-method']);
            }

            $options['class'] = $options['class'] ? 'btn btn-sm ' . $options['class'] : 'btn btn-sm';

            $buttons[] = [
                'title'   => ArrayHelper::getValue($transition, 'name'),
                'options' => $options
            ];
        }

        return $buttons ?? [];
    }

    /**
     * Подготовим объект калькулятора для рассчета стоимости
     * @return Calculator
     */
    public function getCalculator(): Calculator
    {
        $calculator = new Calculator();

        $calculator->width          = $this->width;
        $calculator->height         = $this->height;
        $calculator->length         = $this->length;
        $calculator->cost           = $this->cost;
        $calculator->accessed_cost  = $this->assessed_cost;
        $calculator->shop_id        = $this->shop_id;
        $calculator->partial        = $this->partial;
        $calculator->warehouse_id   = $this->warehouse_id;
        $calculator->payment_method = $this->payment_method;
        $calculator->city           = $this->address->city ?? null;
        $calculator->city_fias_id   = $this->address->city_fias_id ?? null;
        $calculator->weight         = $this->getWeight();
        $calculator->product_count  = $this->getProductsCount();

        return $calculator;
    }

    /**
     * @param array $data
     * @return Order
     */
    public function prepareData(array $data): Order
    {
        $this->load($data);
        $this->weight = $this->weight ? $this->weight * 1000 : $this->getWeight();
        return $this;
    }

    /**
     * @return null|string
     */
    public function getOrderNumber(): ?string
    {
        return YII_ENV_DEV
            ? ((string) $this->id . '-' . (string) Yii::$app->user->getId())
            : (string) $this->id;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Если статус доставки массив, то нужно его конвертнуть в json
            if (is_array($this->delivery_status)) {
                $this->delivery_status = json_encode($this->delivery_status);
            }
            return true;
        }
        return false;
    }
}
