<?php

namespace app\models\search;

use app\components\DbDependencyHelper;
use app\delivery\DeliveryHelper;
use app\models\Helper;
use app\models\Order;
use app\models\Provider;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\grid\CheckboxColumn;

/**
 * OrderSearch represents the model behind the search form of `app\models\Order`.
 */
class LabelSearch extends OrderSearch implements SearchModelInterface
{
    const DATE_FORMAT = 'd.m.Y, H:i';
    const CLASS_NAME = 'LabelSearch';

    public $carrier_key;
    public $pickup_date;

    private $cookieKey = 'userLabelColumns';

    public function formName()
    {
        return '';
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
                'name' => 'Ожидают курьера',
                'params' => [
                    'status' => 'waitingCourier',
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['carrier_key', 'shop_order_number', 'fio', 'status', 'codCost', 'shop_id', 'pickup_date'], 'safe'],
        ];
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
            return (new Order())->getWorkflowStatusName($this->status);
        };

        return $fields;
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
        $query = Order::find()
            ->andWhere('(order.dispatch_number IS NOT NULL OR order.dispatch_number != "") AND (order_delivery.name IS NOT NULL)')
            ->joinWith('delivery')
            ->joinWith('shop')
            ->joinWith('courier');

        $this->load($params);

        $query->andFilterWhere([
            'order.id' => $this->id,
        ]);

        if ($this->pickup_date) {
            $query->andFilterWhere(['courier.pickup_date' => strtotime($this->pickup_date)]);
        }

        $query->andFilterWhere(['like', 'order.shop_order_number', $this->shop_order_number])
            ->andFilterWhere(['like', 'order.fio', $this->fio])
            ->andFilterWhere(['like', 'order_delivery.carrier_key', $this->carrier_key])
            ->andFilterWhere(['like', 'order.status', $this->status])
            ->andFilterWhere(['like', 'order.shop_id', $this->shop_id]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['shop_id' => $user->getAllowedShopIds()]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_at' => SORT_DESC]]
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
            ->select('
                    shop.name as shop_name,
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
                    order_delivery.id as order_delivery_id,
                    SUM(order_product.price * order_product.quantity) as cost')
            ->from('order')
            ->leftJoin('order_delivery', 'order_delivery.order_id = order.id')
            ->leftJoin('shop', 'shop.id = order.shop_id')
            ->leftJoin('order_product', 'order.id = order_product.order_id')
            ->leftJoin('courier', 'courier.id = order.courier_id');

        $this->load($params);

        $query->andWhere('(order.label_url IS NOT NULL OR order.label_url != "") AND (order_delivery.name IS NOT NULL)');

        $query->andFilterWhere([
            'order.id' => $this->id,
        ]);

        if ($this->pickup_date) {
            $query->andFilterWhere(['courier.pickup_date' => strtotime($this->pickup_date)]);
        }

        $query->andFilterWhere(['like', 'order.shop_order_number', $this->shop_order_number])
            ->andFilterWhere(['like', 'order.fio', $this->fio])
            ->andFilterWhere(['like', 'order_delivery.carrier_key', $this->carrier_key])
            ->andFilterWhere(['like', 'order.status', $this->status])
            ->andFilterWhere(['like', 'order.shop_id', $this->shop_id]);

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
     * Список полей для поиска
     * @param Order $searchModel
     * @return array
     */
    public function getSearchColumns(Order $searchModel)
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
                $result[] = $commonSearch->getColumn($column, self::getCallColumns()[$column], $searchModel);
            }
        }

        return $result;
    }

    /**
     * @param string $column
     * @param array $data
     * @param array $model
     * @param bool $asLink
     * @return string
     */
    public function getExportColumnContent(string $column, array $data, $model, $asLink = true): string
    {
        switch ($column) {
            case 'status':
                $text =  (new Order())->getWorkflowStatusName($model[$column]);
                break;
            case 'carrier_key':
                $text = DeliveryHelper::getName($model[$column]);
                break;
            case 'shop_id':
                $text = $model['shop_name'];
                break;
            case 'codCost':
                $text = Order::getOrderCodCost($model['id']);
                break;
            case 'print':
                $text = $model['label_url'];
                break;
            default:
                $text = isset ($model[$column]) ? (string) $model[$column]: '';
        }
        return (string) $text;
    }

    /**
     * Список полей для экспорта
     * @param Order $searchModel
     * @return array
     */
    public function getExportColumns(Order $searchModel)
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
                'name' => Yii::t('app', 'Id'),
                'type' => 'text',
                'default' => true,
                'label' => 'Id',
                'asLink' => true,
                'url' => [
                    'subLink' => 'order/%d',
                    'subData' => 'id'
                ],
                'function' => 'getId',
                'headerOptions' => [
                    'width' => '50px',
                ]
            ],
            'shop_order_number' => [
                'name' => Yii::t('app', 'Shop Number'),
                'type' => 'text',
                'default' => true,
                'label' => 'Shop Number',
                'asLink' => true,
                'url' => [
                    'subLink' => 'order/%d',
                    'subData' => 'id'
                ],
                'function' => 'getShopOrderNumber',
                'headerOptions' => [
                    'width' => '100px',
                ]
            ],
            'fio' => [
                'name' => Yii::t('app', 'Fio'),
                'type' => 'text',
                'default' => true,
                'asLink' => true,
                'url' => [
                    'subLink' => 'order/%d',
                    'subData' => 'id'
                ],
                'label' => 'Fio',
                'function' => 'getFIO',
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
            'codCost' => [
                'name' => Yii::t('app', 'COD'),
                'label' => 'COD',
                'type' => 'price',
                'default' => true,
                'asLink' => true,
                'url' => [
                    'subLink' => 'order/%d',
                    'subData' => 'id'
                ],
                'function' => 'getCOD',
                'headerOptions' => [
                    'width' => '50px',
                ],
            ],
            'status' => [
                'name' => Yii::t('app', 'Status'),
                'type' => 'dropdown',
                'default' => true,
                'asLink' => true,
                'url' => [
                    'subLink' => 'order/%d',
                    'subData' => 'id'
                ],
                'label' => 'Status',
                'function' => 'getCurrentStatusName',
                'list' => (new Order())->getStatuses(),
                'headerOptions' => [
                    'width' => '250px',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
            ],
            'carrier_key' => [
                'name' => Yii::t('app', 'Carrier Key'),
                'type' => 'dropdown',
                'default' => true,
                'asLink' => true,
                'url' => [
                    'subLink' => 'order/%d',
                    'subData' => 'id'
                ],
                'label' => 'Carrier Key',
                'function' => 'getDeliveryCarrierName',
                'list' => Provider::getProviders(),
                'headerOptions' => [
                    'width' => '50px',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
            ],
            'shop_id' => [
                'name' => Yii::t('app', 'Shop'),
                'label' => 'Shop',
                'type' => 'dropdown',
                'function' => 'getShopName',
                'default' => true,
                'asLink' => true,
                'url' => [
                    'subLink' => 'shop/%d/update',
                    'subData' => 'shop_id'
                ],
                'list' => Yii::$app->user->identity->getAllowedShops(),
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [
                    'width' => '300px',
                ],
            ],
            'print' => [
                'name' => Yii::t('app', 'Print'),
                'type' => 'text',
                'default' => true,
                'label' => 'Print',
                'function' => 'getPrintUrl',
                'asLink' => true,
                'url' => [
                    'text' => '<i class="fa fa-save"></i>',
                    'options' => [
                        'class' => 'btn btn-xs btn-default',
                        'target' => '_blank'
                    ],
                ],
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
        ];
    }
}
