<?php

namespace app\models\search;

use app\components\DbDependencyHelper;
use app\delivery\DeliveryHelper;
use app\models\Helper;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Provider;
use app\models\User;
use app\widgets\grid\CheckboxColumn;
use kartik\daterange\DateRangePicker;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Cookie;

/**
 * OrderSearch represents the model behind the search form of `app\models\Order`.
 */
class OrderSearch extends Order implements SearchModelInterface
{
    const DATE_FORMAT = 'd.m.Y, H:i';

    const DATE_TYPES = ['date', 'dateRange'];
    const TEXT_TYPES = ['text'];
    const PRICE_TYPES = ['price'];
    const DROPDOWN_TYPES = ['dropdown'];

    /** @var string Статус звонка */
    public $status_call;
    /** @var string Служба доставки */
    public $carrier_key;
    /** @var string Тип доставки */
    public $type;
    /** @var string Метод оплаты */
    public $payment;
    /** @var string Адрес доставки */
    public $address;
    /** @var string Период создания */
    public $date_created;
    /** @var float Стоимость доставки */
    public $delivery_cost;
    /** @var bool С продуктами */
    public $isWithProducts;
    /** @var array Массив даты */
    public $date_period;
    /** @var array Массив колонок пользователя */
    public $userColumns;

    public function formName()
    {
        return '';
    }

    /**
     * Получить фильтры для сортировки
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = [
            [
                'name'          => 'Новые',
                'params'        => [
                    'status' => 'created',
                    'sort'   => '-id',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ],
                'extraButtons'  => [
                    [
                        'name'    => 'Подтвердить',
                        'options' => [
                            'class'          => 'btn btn-sm btn-success confirm-orders-button',
                            'data-toggle'    => "tooltip",
                            'data-placement' => "bottom",
                            'title'          => "Подтверждение выбранных заказов"
                        ],
                    ],
                ],
            ],
            [
                'name'          => 'На сборке',
                'params'        => [
                    'status' => 'inCollecting',
                    'sort'   => '-id',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
            [
                'name'          => 'Готовы к отгрузке',
                'params'        => [
                    'status' => 'redyForDelivery',
                    'sort'   => '-id',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ],
                'extraButtons'  => [
                    [
                        'name'    => 'Вызвать курьера',
                        'options' => [
                            'id'    => 'courier-call',
                            'class' => 'btn btn-sm btn-info'
                        ],
                    ],
                ],
            ],
            [
                'name'          => 'Ожидают курьера',
                'params'        => [
                    'status' => 'waitingCourier',
                    'sort'   => '-id',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
            [
                'name'          => 'В доставке',
                'params'        => [
                    'status' => 'inDelivery',
                    'sort'   => '-id',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
            [
                'name'          => 'Доставленные',
                'params'        => [
                    'status' => 'delivered',
                    'sort'   => '-id',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
            [
                'name'          => 'Отмененные',
                'params'        => [
                    'status' => 'canceled',
                    'sort'   => '-id',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
            [
                'name'    => 'Все',
                'params'  => null,
                'options' => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
            [
                'name'          => 'Архивные',
                'params'        => [
                    'is_archive' => true,
                    'sort'       => '-id',
                ],
                'excludeParams' => ['sort'],
                'extraButtons'  => [
                    [
                        'name'    => 'Разархивировать заказы',
                        'options' => [
                            'id'    => 'un-archive-orders',
                            'class' => 'btn btn-sm btn-info'
                        ],
                    ],
                ],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
        ];

        if ($this->problemOrdersCount()) {
            $filters[] = [
                'name'          => 'Проблемные',
                'params'        => [
                    'status' => 'deliveryError',
                    'sort'   => '-id',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-danger',
                ],
                'desc'          => 'Заказы, которые находятся в статусе с ошибкой, невозможно перевести в работу. Система исправляет ошибки автоматически в течении некоторого времени. Пожалуйста, ожидайте.'
            ];
        }

        return $filters;
    }

    /**
     * Создание ссылки для фильтра
     *
     * @param array $filter
     * @return string
     */
    public function createFilterUrl(array $filter)
    {
        $url = '?';
        if (isset($filter['params']) && is_array($filter['params'])) {
            $params = [];
            foreach ($filter['params'] as $paramKey => $paramVal) {
                $params[] = $paramKey . '=' . $paramVal;
            }
            $url .= implode('&', $params);
        }

        return $url;
    }

    /**
     * @param array $filter
     * @return bool
     */
    public function isFilterActive(array $filter)
    {
        $active    = true;
        $params    = $filter['params'];
        $getParams = Yii::$app->request->get();
        foreach ($getParams as $paramKey => $paramValue) {
            if (substr($paramKey, 0, 1) === '_') {
                unset($getParams[$paramKey]);
            }
        }

        if (is_array($params)) {
            foreach ($params as $paramKey => $paramVal) {
                if (!in_array($paramKey, $filter['excludeParams'])) {
                    if (!isset($getParams[$paramKey]) || $getParams[$paramKey] != $paramVal) {
                        $active = false;
                    }
                }

            }
        } elseif ($getParams != false) {
            $active = false;
        }

        return $active;
    }

    /**
     * Правила модели
     *
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at'], 'integer'],
            [[
                'carrier_key',
                'is_archive',
                'fio',
                'phone',
                'email',
                'status',
                'codCost',
                'address',
                'cost',
                'status_call',
                'delivery_cost',
                'payment_method',
                'shop_id',
                'type',
                'payment',
                'date_created',
                'shop_order_number',
                'dispatch_number',
                'isWithProducts',
                'userColumns'
            ], 'safe'],
        ];
    }

    /**
     * Список заголовков полей модели
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'ordetStatus'  => Yii::t('app', 'Order status'),
            'type'         => Yii::t('app', 'Delivery method'),
            'carrier_key'  => Yii::t('app', 'Carrier Key'),
            'created_date' => Yii::t('app', 'Date'),
            'shop_ids'     => Yii::t('app', 'Shop ID'),
        ];
    }

    /**
     * Количество порблемных заказов
     *
     * @return int
     */
    private function problemOrdersCount(): int
    {
        $query = Order::find();

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere([
            'status'  => Order::WORKFLOW_KEY . Order::STATUS_DELIVERY_ERROR,
            'shop_id' => $user->getAllowedShopIds(),
            'is_archive' => false
        ]);
        return $query->count();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Order::find()->joinWith(['delivery', 'shop', 'address']);
        $this->load($params);

        $query->andFilterWhere([
            'order.id' => $this->id
        ]);

        $query->andFilterWhere([
            'order.is_archive' => (boolean)$this->is_archive
        ]);

        if ($this->created_at) {
            $dates = explode('-', $this->created_at);
            if (count($dates) == 2) {
                $query->andFilterWhere([
                    '>', 'order.created_at', strtotime(trim($dates[0]))
                ]);
                $query->andFilterWhere([
                    '<', 'order.created_at', strtotime(trim($dates[1])) + 86400
                ]);
            }
        }

        $query
            ->andFilterWhere(['like', 'order.fio', $this->fio])
            ->andFilterWhere(['like', 'order.shop_order_number', $this->shop_order_number])
            ->andFilterWhere(['like', 'order.phone', $this->getClearPhone()])
            ->andFilterWhere(['like', 'order.comment', $this->comment])
            ->andFilterWhere(['like', 'order.email', $this->email])
            ->andFilterWhere(['like', 'order.status', $this->status])
            ->andFilterWhere(['like', 'order.payment_method', $this->payment])
            ->andFilterWhere(['like', 'order.dispatch_number', $this->dispatch_number])
            ->andFilterWhere(['like', 'address.full_address', $this->address])
            ->andFilterWhere(['like', 'order_delivery.carrier_key', $this->carrier_key])
            ->andFilterWhere(['like', 'order_delivery.cost', $this->delivery_cost])
            ->andFilterWhere(['like', 'order_delivery.type', $this->type]);

        $query->andFilterWhere([
            'shop.id' => $this->shop_id
        ]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['shop_id' => $user->getAllowedShopIds()]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['created_at' => SORT_DESC]]
        ]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ArrayDataProvider
     */
    public function export($params)
    {
        $query = (new Query())
            ->select('order.id as id, 
                    order.shop_order_number as shop_order_number, 
                    order.phone as phone,
                    order.email as email,
                    address.full_address as address,
                    order.payment_method as payment,
                    order.fio as fio, 
                    order.status as status, 
                    order.payment_method as payment_method, 
                    order.created_at as created_at, 
                    shop.name as shop_name, 
                    order_delivery.carrier_key as carrier_key, 
                    order_delivery.cost as delivery_cost, 
                    order.dispatch_number as dispatch_number, 
                    order_delivery.type as type, 
                    order_delivery.id as order_delivery_id')
            ->from('order')
            ->leftJoin('order_delivery', 'order.id = order_delivery.order_id')
            ->leftJoin('shop', 'shop.id = order.shop_id')
            ->leftJoin('order_product', 'order.id = order_product.order_id')
            ->leftJoin('address', 'address.id = order.address_id');

        $this->load($params);

        $query->andFilterWhere(['order.id' => $this->id]);

        if ($this->created_at) {
            $dates = explode('-', $this->created_at);
            $query->andFilterWhere([
                '>', 'order.created_at', strtotime(trim($dates[0]))
            ]);
            $query->andFilterWhere([
                '<', 'order.created_at', strtotime(trim($dates[1])) + 86400
            ]);
        }

        $query
            ->andFilterWhere(['like', 'order.fio', $this->fio])
            ->andFilterWhere(['like', 'order.shop_order_number', $this->shop_order_number])
            ->andFilterWhere(['like', 'order.phone', $this->getClearPhone()])
            ->andFilterWhere(['like', 'order.comment', $this->comment])
            ->andFilterWhere(['like', 'order.status', $this->status])
            ->andFilterWhere(['like', 'order.payment_method', $this->payment])
            ->andFilterWhere(['like', 'order.dispatch_number', $this->dispatch_number])
            ->andFilterWhere(['like', 'address.full_address', $this->address])
            ->andFilterWhere(['like', 'order_delivery.carrier_key', $this->carrier_key])
            ->andFilterWhere(['like', 'order_delivery.type', $this->type])
            ->andFilterWhere(['like', 'order_delivery.cost', $this->delivery_cost]);

        $query->andFilterWhere(['shop.id' => $this->shop_id]);
        $query->groupBy(['order.id']);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['shop_id' => $user->getAllowedShopIds()]);

        $models = Yii::$app->getDb()->cache(function ($db) use ($query) {
            return $query->all();
        }, Helper::MIN_CACHE_VALUE, DbDependencyHelper::generateDependency(Order::find()));

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
        ]);

        return $dataProvider;
    }

    /**
     * @param string $column
     * @param array $data
     * @param Order $model
     * @param bool $asLink
     * @return string
     */
    public function getColumnContent(string $column, array $data, $model, $asLink = true): string
    {
        $fName = $data['function'];
        $text  = $model->$fName() ? $model->$fName() : '';
        if ($text && $asLink) {
            return Html::a($model->$fName(), Url::to(['view', 'id' => $model->id]));
        }
        return $text;
    }

    /**
     * @param string $column
     * @param array $data
     * @param Order $model
     * @param bool $asLink
     * @return string
     */
    public function getExportColumnContent(string $column, array $data, $model, $asLink = true): string
    {
        switch ($column) {
            case 'status':
                $text = (new Order())->getWorkflowStatusName($model[$column]);
                break;
            case 'carrier_key':
                $text = DeliveryHelper::getName($model[$column]);
                break;
            case 'codCost':
                $text = Order::getOrderCodCost($model['id']);
                break;
            case 'cost':
                $text = Order::getOrderCost($model['id']);
                break;
            case 'shop_id':
                $text = $model['shop_name'];
                break;
            case 'type':
                $text = Helper::getDeliveryTypeName($model[$column]);
                break;
            case 'payment':
                $text = Helper::getPaymentMethodName($model[$column]);
                break;
            case 'phone':
                $text = (new Order())->getNormalizePhone($model[$column]);
                break;
            case 'created_at':
                $text = date(self::DATE_FORMAT, $model[$column]);
                break;
            default:
                $text = isset ($model[$column]) ? (string)$model[$column] : '';
        }

        return (string)$text;
    }

    /**
     * @param string $column
     * @param array $data
     * @param Order $model
     * @param array $dataList
     * @return string|null
     */
    private function getColumnFilter(string $column, array $data, $model, $dataList): ?string
    {
        if (isset($data['type']) && $data['type'] == 'dateRange') {
            return Html::tag('div',
                DateRangePicker::widget([
                    'name'          => $column,
                    'value'         => $model->$column,
                    'convertFormat' => false,
                    'useWithAddon'  => false,
                    'options'       => ['class' => 'form-control input-xs', 'id' => 'order-create-date'],
                    'pluginOptions' => [
                        'locale' => [
                            'format'    => 'DD.MM.YYYY',
                            'separator' => ' - ',
                        ]
                    ]
                ])
            );
        }
        if (isset($data['type']) && $data['type'] == 'dropdown') {
            return Html::tag('div', Html::dropDownList(
                $column,
                $model->$column,
                ArrayHelper::merge(
                    ['' => ''],
                    $dataList
                ),
                [
                    'class' => 'form-control input-xs' . (($model->$column === null || $model->$column === '') ? '' : ' selected'),
                ]
            ),
                ['class' => 'select_wrapper']
            );
        }
        return null;
    }

    /**
     * @param string $column
     * @param array $data
     * @param $model
     * @param bool $isExport
     * @param bool $asLink
     * @return array
     */
    private function getColumn(string $column, array $data, $model, $isExport = false, $asLink = true): array
    {
        $columnSearch = [
            'attribute' => $column,
            'label'     => Yii::t('app', $data['label']),
            'content'   => function ($model) use ($column, $data, $asLink) {
                return $this->getExportColumnContent($column, $data, $model, $asLink);
            }
        ];

        if (!$isExport) {
            $columnSearch['content']                                   = function ($model) use ($column, $data, $asLink) {
                return $this->getColumnContent($column, $data, $model, $asLink);
            };
            $columnSearch['filterOptions']                             = isset($data['filterOptions']) ? $data['filterOptions'] : [];
            $columnSearch['contentOptions']                            = isset($data['contentOptions']) ? $data['contentOptions'] : [];
            $columnSearch['filter']                                    = $this->getColumnFilter($column, $data, $model, isset($data['list']) ? $data['list'] : null);
            $columnSearch['headerOptions']                             = isset($data['headerOptions']) ? $data['headerOptions'] : [];
            $columnSearch['headerOptions']['data-resizable-column-id'] = $column;
        }

        return $columnSearch;
    }

    /**
     * Список полей для поиска
     *
     * @param Order $searchModel
     * @return array
     */
    public function getSearchColumns(Order $searchModel)
    {
        $result[] = [
            'class'         => CheckboxColumn::className(),
            'headerOptions' => [
                'width'                    => '40px',
                'data-resizable-column-id' => 'checker'
            ],
        ];
        foreach ($this->getUserColumns() as $column => $data) {
            if ($data) {
                $result[] = $this->getColumn($column, self::getOrderColumns()[$column], $searchModel);
            }
        }

        return $result;
    }

    /**
     * Список полей для выгрузки
     *
     * @param Order $searchModel
     * @return array
     */
    public function getExportColumns(Order $searchModel)
    {
        $result = [];
        foreach ($this->getUserColumns() as $column => $data) {
            if ($data) {
                $result[] = $this->getColumn($column, self::getOrderColumns()[$column], $searchModel, true, false);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getOrderColumns(): array
    {
        return [
            'created_at'        => [
                'name'          => Yii::t('app', 'Created At'),
                'type'          => 'dateRange',
                'default'       => true,
                'label'         => 'Created At',
                'function'      => 'getCreatedDate',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'id'                => [
                'name'          => Yii::t('app', 'Fastery number'),
                'label'         => 'Fastery number',
                'default'       => true,
                'type'          => 'text',
                'function'      => 'getId',
                'headerOptions' => [
                    'width' => '50px',
                ],
            ],
            'shop_order_number' => [
                'name'          => Yii::t('app', 'Shop number'),
                'label'         => 'Shop number',
                'default'       => true,
                'type'          => 'text',
                'function'      => 'getShopOrderNumber',
                'headerOptions' => [
                    'width' => '50px',
                ],
            ],
            'dispatch_number'   => [
                'name'          => Yii::t('app', 'Dispatch number'),
                'label'         => 'Dispatch number',
                'default'       => false,
                'type'          => 'text',
                'function'      => 'getDispatchNumber',
                'headerOptions' => [
                    'width' => '150px',
                ],
            ],
            'cost'              => [
                'name'          => Yii::t('app', 'Order Amount'),
                'label'         => 'Order Amount',
                'default'       => true,
                'function'      => 'getOrderProductCost',
                'type'          => 'price',
                'headerOptions' => [
                    'width' => '150px',
                ],
            ],
            'status'            => [
                'name'           => Yii::t('app', 'Status'),
                'label'          => 'Status',
                'type'           => 'dropdown',
                'default'        => true,
                'function'       => 'getCurrentStatusName',
                'list'           => (new Order())->getStatuses(),
                'headerOptions'  => [
                    'width' => '250px',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],
                'filterOptions'  => [
                    'style' => 'position: relative;',
                ],
            ],
            'fio'               => [
                'name'           => Yii::t('app', 'fio'),
                'label'          => 'fio',
                'type'           => 'text',
                'default'        => true,
                'function'       => 'getFIO',
                'headerOptions'  => [
                    'width' => '400px',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],

            ],
            'email'             => [
                'name'          => Yii::t('app', 'Email'),
                'label'         => 'Email',
                'type'          => 'text',
                'default'       => false,
                'function'      => 'getEmail',
                'headerOptions' => [
                    'width' => '200px',
                ],
            ],
            'phone'             => [
                'name'          => Yii::t('app', 'Phone'),
                'label'         => 'Phone',
                'type'          => 'text',
                'function'      => 'getPhone',
                'default'       => true,
                'headerOptions' => [
                    'width' => '250px',
                ],
            ],
            'address'           => [
                'name'          => Yii::t('app', 'Address'),
                'label'         => 'Address',
                'type'          => 'text',
                'function'      => 'getAddressFull',
                'default'       => false,
                'headerOptions' => [
                    'width' => '300px',
                ],
            ],
            'type'              => [
                'name'           => Yii::t('app', 'Delivery method'),
                'label'          => 'Delivery method',
                'type'           => 'dropdown',
                'default'        => false,
                'function'       => 'getDeliveryType',
                'list'           => (new OrderDelivery())->getDeliveryTypes(),
                'headerOptions'  => [
                    'width' => '150px',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],
                'filterOptions'  => [
                    'style' => 'position: relative;',
                ],
            ],
            'carrier_key'       => [
                'name'           => Yii::t('app', 'SD'),
                'label'          => 'SD',
                'type'           => 'dropdown',
                'default'        => true,
                'function'       => 'getDeliveryCarrierName',
                'list'           => Provider::getProviders(),
                'headerOptions'  => [
                    'width' => '50px',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],
                'filterOptions'  => [
                    'style' => 'position: relative;',
                ],
            ],
            'status_call'       => [
                'name'           => Yii::t('app', 'Status Call'),
                'label'          => 'Status Call',
                'type'           => 'empty',
                'default'        => false,
                'function'       => 'getStatusCall',
                'headerOptions'  => [
                    'width' => '50px',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],
            ],
            'codCost'           => [
                'name'          => Yii::t('app', 'COD'),
                'label'         => 'COD',
                'type'          => 'price',
                'default'       => true,
                'function'      => 'getCOD',
                'headerOptions' => [
                    'width' => '50px',
                ],
            ],
            'payment'           => [
                'name'          => Yii::t('app', 'Payment method'),
                'label'         => 'Payment method',
                'type'          => 'dropdown',
                'default'       => false,
                'function'      => 'getPaymentMethod',
                'list'          => (new Order())->getPaymentMethods(),
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [
                    'width' => '200px',
                ],
            ],
            'delivery_cost'     => [
                'name'          => Yii::t('app', 'Delivery cost'),
                'label'         => 'Delivery cost',
                'type'          => 'price',
                'default'       => false,
                'function'      => 'getDeliveryCost',
                'headerOptions' => [
                    'width' => '50px',
                ],
            ],
            'shop_id'           => [
                'name'          => Yii::t('app', 'Shop'),
                'label'         => 'Shop',
                'type'          => 'dropdown',
                'function'      => 'getShopName',
                'default'       => true,
                'list'          => Yii::$app->user->identity->getAllowedShops(),
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [
                    'width' => '300px',
                ],
            ],
        ];
    }

    /**
     * Получить наложенный платеж
     *
     * @param null $cost
     * @param null $delivery
     * @param null $payment
     * @return mixed
     */
    public function getCodCost($cost = null, $delivery = null, $payment = null)
    {
        return $this->_codCost;
    }

    /**
     * Установить наложенный платеж
     *
     * @param $codCost
     */
    public function setCodCost($codCost)
    {
        $this->_codCost = $codCost;
    }

    /**
     * Есть ли заказы в проблемных статусах
     *
     * @return bool
     */
    public function hasDeliveryErrorOrders()
    {
        $query = Order::find()->where(['status' => (new Order())->getWorkflowStatusId(Order::STATUS_DELIVERY_ERROR)])->count();
        return $query ? true : false;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setDatePeriod(string $data)
    {
        $result = [];
        foreach (explode('-', $data) as $date) {
            $result[] = strtotime(trim($date));
        }
        $this->date_period = $result;
        return $this;
    }

    /**
     * @return array
     */
    public function getUserColumns(): array
    {
        $cookies = Yii::$app->request->cookies;
        return json_decode($cookies->getValue('userColumns', self::getDefaultUserColumns()), true);
    }

    /**
     * @return string
     */
    public function getDefaultUserColumns(): string
    {
        $result = [];
        foreach (self::getOrderColumns() as $column => $data) {
            $result[$column] = $data['default'] ? 1 : 0;
        }
        return json_encode($result);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setUserColumns(array $data)
    {
        $this->userColumns = $data;
        $cookies           = Yii::$app->request->cookies;
        $cookies->readOnly = false;
        if ($cookies->has('userColumns')) {
            $cookies->remove('userColumns');
        }

        $nC        = new Cookie();
        $nC->name  = 'userColumns';
        $nC->value = json_encode($data);
        $nC->path  = '/';
        Yii::$app->getResponse()->getCookies()->add($nC);

        return $this;
    }
}
