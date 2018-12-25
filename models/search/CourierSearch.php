<?php

namespace app\models\search;

use app\delivery\DeliveryHelper;
use app\models\Courier;
use app\models\Provider;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\grid\CheckboxColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * RegistrySearch represents the model behind the search form of `app\models\Registry`.
 */
class CourierSearch extends Courier
{
    const DATE_FORMAT = 'd.m.Y';
    const CLASS_NAME = 'CourierSearch';

    public $warehouse;
    public $orders_count;
    public $shop_id;
    private $cookieKey = 'userCourierColumns';

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'number', 'warehouse_id', 'pickup_date', 'courier_call', 'created_at', 'updated_at'], 'integer'],
            [['registry_label_url', 'carrier_key', 'warehouse', 'orders_count', 'shop_id'], 'safe'],
        ];
    }

    public function getFilters()
    {
        return [
            [
                'name' => 'Отгрузки сегодня',
                'params' => [
                    'pickup_date' => date('d.m.Y', time()),
                    'sort' => 'id',
                ],
                'excludeParams' => ['sort'],
                'options' => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
            [
                'name' => 'Все',
                'params' => null,
                'options' => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
        ];
    }

    /**
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
        $active = true;
        $params = $filter['params'];
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Courier::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['pickup_date' => SORT_DESC]],
        ]);

        $this->load($params);

        $query->joinWith(['orders']);
        $query->groupBy('courier.id');

        $query->andFilterWhere([
            'courier.id' => $this->id,
            'courier.number' => $this->number,
            'courier.warehouse_id' => $this->warehouse_id,
            'courier.courier_call' => $this->courier_call,
            'courier.created_at' => $this->created_at,
            'courier.updated_at' => $this->updated_at,
            'order.shop_id' => $this->shop_id,
        ]);

        if($this->pickup_date) {
            $query->andFilterWhere([
                '>=', 'courier.pickup_date', strtotime($this->pickup_date)
            ]);
            $query->andFilterWhere([
                '<', 'courier.pickup_date', strtotime($this->pickup_date) + 86400
            ]);
        }

        $query->andFilterWhere(['like', 'registry_label_url', $this->registry_label_url])
            ->andFilterWhere(['like', 'carrier_key', $this->carrier_key]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['courier.warehouse_id' => $user->getAllowedWarehouseIds()]);
        $query->andFilterWhere(['shop_id' => $user->getAllowedShopIds()]);

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
            ->select('
                    address.full_address as warehouse,
                    COUNT(*) as orders_count,
                    shop.name as shop_name,
                    courier.*')
            ->from('courier')
            ->leftJoin('order', 'courier.id = order.courier_id')
            ->leftJoin('shop', 'shop.id = order.shop_id')
            ->leftJoin('warehouse', 'courier.warehouse_id = warehouse.id')
            ->leftJoin('address', 'warehouse.address_id = address.id');

        $this->load($params);

        $query->groupBy('courier.id');

        $query->andFilterWhere([
            'courier.id' => $this->id,
            'courier.number' => $this->number,
            'courier.warehouse_id' => $this->warehouse_id,
            'courier.courier_call' => $this->courier_call,
            'courier.created_at' => $this->created_at,
            'courier.updated_at' => $this->updated_at,
            'order.shop_id' => $this->shop_id,
        ]);

        if($this->pickup_date) {
            $dates = explode('-', $this->created_at);
            $query->andFilterWhere([
                '>', 'courier.pickup_date', strtotime($this->pickup_date)
            ]);
            $query->andFilterWhere([
                '<', 'courier.pickup_date', strtotime($this->pickup_date) + 86400
            ]);
        }

        $query->andFilterWhere(['like', 'registry_label_url', $this->registry_label_url])
            ->andFilterWhere(['like', 'carrier_key', $this->carrier_key]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['courier.warehouse_id' => $user->getAllowedWarehouseIds()]);
        $query->andFilterWhere(['shop_id' => $user->getAllowedShopIds()]);

        $models = $query->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
        ]);

        return $dataProvider;
    }

    /**
     * Список полей для поиска
     * @param Courier $searchModel
     * @return array
     */
    public function getSearchColumns(Courier $searchModel)
    {
        $result[] = [
            'class' => CheckboxColumn::className(),
            'headerOptions' => [
                'width' => '40px',
                'data-resizable-column-id' => 'checker'
            ],
        ];
        $commonSearch = new CommonSearch();
        foreach ($commonSearch->getUserColumns(self::getCallColumns(), $this->cookieKey) as $column => $data) {
            if ($data) {
                $result[] = $commonSearch->getColumn($column, self::getCallColumns()[$column], $searchModel, false);
            }
        }

        return $result;
    }

    /**
     * @param string $column
     * @param array $data
     * @param $model
     * @param bool $asLink
     * @return string
     */
    public function getExportColumnContent(string $column, array $data, $model, $asLink = true): string
    {
        switch ($column) {
            case 'pickup_date':
                $text = date(self::DATE_FORMAT, $model[$column]);
                break;
            case 'shop_id':
                $text = $model['shop_name'];
                break;
            case 'carrier_key':
                $text = DeliveryHelper::getName($model['shop_name']);
                break;
            case 'courier_call':
                $text = $model[$column] ? Yii::t('app', 'Yes') : Yii::t('app', 'No');
                break;
            case 'download':
                $text = Url::to(['courier/download', 'id' => $model['id']], true);
                break;
            default:
                $text = isset ($model[$column]) ? (string) $model[$column]: '';
        }
        return (string) $text;
    }

    /**
     * Список полей для экспорта
     * @param Courier $searchModel
     * @return array
     */
    public function getExportColumns(Courier $searchModel)
    {
        $result = [];
        $commonSearch = new CommonSearch();
        foreach ($commonSearch->getUserColumns(self::getCallColumns(), $this->cookieKey) as $column => $data) {
            if ($data) {
                $result[] = $commonSearch->getColumn($column, self::getCallColumns()[$column], $searchModel, true, false, self::CLASS_NAME);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getCallColumns(): array
    {
        return [
            'id' => [
                'name' => Yii::t('app', 'ID'),
                'type' => 'text',
                'default' => true,
                'label' => 'Id',
                'function' => 'getId',
                'asLink' => true,
                'url' => [
                    'href' => '#',
                    'options' => [
                        'class' => 'courier-orders',
                    ],
                    'data' => [
                        'data-courier-id' => 'getId'
                    ]
                ],
                'headerOptions' => [
                    'width' => '50px',
                ]
            ],
            'pickup_date' => [
                'name' => Yii::t('app', 'Pickup Date'),
                'type' => 'date',
                'default' => true,
                'label' => 'Pickup Date',
                'function' => 'getPickupDate',
                'asLink' => true,
                'url' => [
                    'href' => '#',
                    'options' => [
                        'class' => 'courier-orders',
                    ],
                    'data' => [
                        'data-courier-id' => 'getId'
                    ]
                ],
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'orders_count' => [
                'name' => Yii::t('app', 'Orders Count'),
                'type' => 'text',
                'default' => true,
                'label' => 'Orders Count',
                'asLink' => true,
                'url' => [
                    'href' => '#',
                    'options' => [
                        'class' => 'courier-orders',
                    ],
                    'data' => [
                        'data-courier-id' => 'getId'
                    ]
                ],
                'function' => 'getOrdersCount',
                'headerOptions' => [
                    'width' => '50px',
                ]
            ],
            'carrier_key' => [
                'name' => Yii::t('app', 'Carrier Key'),
                'type' => 'dropdown',
                'default' => true,
                'label' => 'Carrier Key',
                'list' => ArrayHelper::merge(['' => ''], Provider::getProviders()),
                'function' => 'getCarrierKey',
                'asLink' => true,
                'url' => [
                    'href' => '#',
                    'options' => [
                        'class' => 'courier-orders',
                    ],
                    'data' => [
                        'data-courier-id' => 'getId'
                    ]
                ],
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [
                    'width' => '100px',
                ]
            ],
            'warehouse' => [
                'name' => Yii::t('app', 'Warehouse'),
                'type' => 'text',
                'default' => true,
                'label' => 'Warehouse',
                'function' => 'getWarehouseName',
                'asLink' => true,
                'url' => [
                    'href' => '#',
                    'options' => [
                        'class' => 'courier-orders',
                    ],
                    'data' => [
                        'data-courier-id' => 'getId'
                    ]
                ],
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'courier_call' => [
                'name' => Yii::t('app', 'Courier Confirm'),
                'type' => 'text',
                'default' => true,
                'label' => 'Courier Confirm',
                'function' => 'getCourierCall',
                'asLink' => true,
                'url' => [
                    'href' => '#',
                    'options' => [
                        'class' => 'courier-orders',
                    ],
                    'data' => [
                        'data-courier-id' => 'getId'
                    ]
                ],
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'shop_id' => [
                'name' => Yii::t('app', 'Shop name'),
                'type' => 'dropdown',
                'default' => true,
                'list' => Yii::$app->user->identity->getAllowedShops(),
                'label' => 'Shop name',
                'function' => 'getShopName',
                'asLink' => true,
                'url' => [
                    'href' => '#',
                    'options' => [
                        'class' => 'courier-orders',
                    ],
                    'data' => [
                        'data-courier-id' => 'getId'
                    ]
                ],
                'headerOptions' => [
                    'width' => '100px',
                ],
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
            ],
            'download' => [
                'name' => Yii::t('app', 'Print'),
                'label' => 'Print',
                'type' => 'text',
                'function' => 'getPrintUrl',
                'asLink' => true,
                'url' => [
                    'text' => '<i class="fa fa-save"></i>',
                    'options' => [
                        'class' => 'btn btn-xs btn-default',
                        'target' => '_blank'
                    ],
                ],
                'default' => true,
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [
                    'width' => '30px',
                ],
            ],
        ];
    }
}
