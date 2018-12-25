<?php
namespace app\models\search;

use app\models\Helper\Document;
use app\models\Helper\Status;
use app\models\Repository\Invoice;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use app\widgets\grid\CheckboxColumn;

class InvoiceSearch extends Invoice implements SearchModelInterface
{
    private $cookieKey = 'chargeColumns';

    public $order_count;
    public $registry_number;
    public $shop_id;

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
            [['created_at', 'type', 'shop_id'], 'integer'],
            [['sum', 'order_count', 'status', 'status'], 'number'],
            [['number', 'registry_number'], 'string'],
            [['name'], 'safe'],
        ];
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
     * Получить фильтры для сортировки
     *
     * @return array
     */
    public function getFilters(): array
    {
        $filters = [
            [
                'name'          => 'Платежки',
                'params'        => [
                    'type' => '2',
                    'sort'   => '-created_at',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ],
            ],
            [
                'name'          => 'Счета',
                'params'        => [
                    'type' => '1',
                    'sort'   => '-created_at',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ],
            ],
            [
                'name'          => 'Не оплаченные',
                'params'        => [
                    'status' => '0',
                    'sort'   => '-created_at',
                ],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ],
            ],
            [
                'name'          => 'Все',
                'params'        => [],
                'excludeParams' => ['sort'],
                'options'       => [
                    'class' => 'btn btn-sm btn-default',
                ],
            ],
        ];

        return $filters;
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

        if (is_array($params) && !empty($params)) {
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
        $query = Invoice::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['created_at' => SORT_DESC]]
        ]);

        $this->load($params);

        if ($this->created_at) {
            $dates = explode('-', $this->created_at);
            $query->andFilterWhere([
                '>', 'invoice.created_at', strtotime(trim($dates[0]))
            ]);
            $query->andFilterWhere([
                '<', 'invoice.created_at', strtotime(trim($dates[1])) + 86400
            ]);
        }

        $query->andFilterWhere(['invoice.type' => $this->type]);
        $query->andFilterWhere(['invoice.status' => $this->status]);
//        $query->andFilterWhere(['invoice.shop_id' => $this->shop_id]);
        $query->andFilterWhere(['like', 'invoice.sum', $this->sum]);
        if (!empty($this->registry_number)) {
            $query->joinWith(['registry']);
            $query->andFilterWhere(['like', 'registry.number', $this->registry_number]);
        }
        $query->joinWith(['shops']);
        if (!empty($this->shop_id)) {
            $query->andFilterWhere(['shop.id' => $this->shop_id]);
        }
        $query->andFilterWhere(['like', 'invoice.number', $this->number]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['shop.id' => $user->getAllowedShopIds()]);

        return $dataProvider;
    }

    /**
     * Список полей для поиска
     *
     * @param InvoiceSearch $searchModel
     * @return array
     */
    public function getSearchColumns(InvoiceSearch $searchModel)
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
     * @return array
     */
    public static function getCallColumns(): array
    {
        return [
            'created_at' => [
                'name' => Yii::t('shop', 'Created date'),
                'type' => 'dateRange',
                'default' => true,
                'asLink' => true,
                'label' => 'Created date',
                'function' => 'getDocumentDate',
                'headerOptions' => []
            ],
            'type' => [
                'name' => Yii::t('shop', 'Document type'),
                'label' => 'Document type',
                'type' => 'dropdown',
                'function' => 'getDocumentType',
                'default' => true,
                'asLink' => true,
                'list' => (new Document())->getTypeList(),
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [],
            ],
            'status' => [
                'name' => Yii::t('shop', 'Status'),
                'label' => 'Status',
                'type' => 'dropdown',
                'function' => 'getDocumentStatus',
                'default' => true,
                'asLink' => true,
                'list' => (new Status())->getStatusList(),
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [],
            ],
            'number' => [
                'name' => Yii::t('shop', 'Invoice number'),
                'type' => 'text',
                'label' => 'Invoice number',
                'default' => true,
                'function' => 'getDocumentNumber',
                'asLink' => true,
                'headerOptions' => []
            ],
            'registry_number' => [
                'name' => Yii::t('shop', 'Registry number'),
                'type' => 'text',
                'label' => 'Registry number',
                'default' => true,
                'asLink' => true,
                'function' => 'getRegistryNumber',
                'headerOptions' => []
            ],
            'sum' => [
                'name' => Yii::t('shop', 'Sum'),
                'type' => 'price',
                'label' => 'Sum',
                'default' => true,
                'asLink' => true,
                'function' => 'getDocumentSum',
                'headerOptions' => []
            ],
            'order_count' => [
                'name' => Yii::t('shop', 'Count orders'),
                'type' => 'text',
                'label' => 'Count orders',
                'default' => true,
                'asLink' => true,
                'function' => 'getOrderCount',
                'headerOptions' => []
            ],
            'shop_id' => [
                'name' => Yii::t('shop', 'Shop name'),
                'label' => 'Shop name',
                'type' => 'dropdown',
                'function' => 'getDocumentShopName',
                'default' => true,
                'asLink' => true,
                'list' => Yii::$app->user->identity->getAllowedShops(),
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [],
            ],
        ];
    }
}
