<?php

namespace app\models\search;

use app\models\Product;
use app\models\User;
use app\widgets\grid\CheckboxColumn;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;

/**
 * ProductSearch represents the model behind the search form of `app\models\Product`.
 */
class ProductSearch extends Product implements SearchModelInterface
{
    const CLASS_NAME = 'ProductSearch';
    const DATE_FORMAT = 'd.m.Y, H:i';

    public $shopName = '';
    public $inStock;
    public $status;

    private $cookieKey = 'userProductColumns';

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
            [['id', 'shop_id', 'count', 'status'], 'integer'],
            [['name', 'barcode', 'shopName', 'inStock'], 'safe'],
            [['price', 'accessed_price', 'weight'], 'number'],
        ];
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
        $query = Product::find()->joinWith('shop');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        $query->andFilterWhere([
            'product.id' => $this->id,
            'product.price' => $this->price,
            'product.accessed_price' => $this->accessed_price,
            'product.weight' => $this->weight,
            'product.count' => $this->count,
            'shop.id' => $this->shop_id,
        ]);

        if ($this->inStock) {
            $query->andFilterWhere(['>', 'product.count', 0]);
        }

        if ($this->status !== null) {
            $query->andFilterWhere(['product.status' => $this->status]);
        } else {
            $query->andFilterWhere(['>', 'product.status', 0]);
        }

        $query->andFilterWhere(['like', 'product.name', $this->name])
            ->andFilterWhere(['like', 'product.barcode', $this->barcode])
            ->andFilterWhere(['like', 'shop.name', $this->shopName]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
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
                    shop.name as shop_name,
                    product.*')
            ->from('product')
            ->leftJoin('shop', 'shop.id = product.shop_id');

        $this->load($params);

        $query->andFilterWhere([
            'product.id' => $this->id,
            'product.price' => $this->price,
            'product.accessed_price' => $this->accessed_price,
            'product.weight' => $this->weight,
            'product.count' => $this->count,
            'shop.id' => $this->shop_id,
        ]);

        if ($this->inStock) {
            $query->andFilterWhere(['>', 'product.count', 0]);
        }

        if ($this->status !== null) {
            $query->andFilterWhere(['product.status' => $this->status]);
        } else {
            $query->andFilterWhere(['>', 'product.status', 0]);
        }

        $query->andFilterWhere(['like', 'product.name', $this->name])
            ->andFilterWhere(['like', 'product.barcode', $this->barcode])
            ->andFilterWhere(['like', 'shop.name', $this->shopName]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        if ($user->getAllowedShopIds() !== []) {
            $query->andFilterWhere(['shop.id' => $user->getAllowedShopIds()]);
        }

        $query->limit(1000);

        $models = $query->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
        ]);

        return $dataProvider;
    }

    public function getFilters()
    {
        return [
            [
                'name' => 'В наличии',
                'params' => [
                    'inStock' => true,
                    'sort' => 'id',
                ],
                'excludeParams' => ['sort'],
                'options' => [
                    'class' => 'btn btn-sm btn-default',
                ]
            ],
            [
                'name' => 'Архивные',
                'params' => [
                    'status' => 0,
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
     * @param string $column
     * @param array $data
     * @param array $model
     * @param bool $asLink
     * @return string
     */
    public function getExportColumnContent(string $column, array $data, $model, $asLink = true): string
    {
        switch ($column) {
            case 'created_at':
                $text = date(self::DATE_FORMAT, $model[$column]);
                break;
            case 'shop_id':
                $text = $model['shop_name'];
                break;
            case 'weight':
                $text = $model[$column] / 1000;
                break;
            default:
                $text = isset ($model[$column]) ? (string) $model[$column]: '';
        }
        return (string) $text;
    }


    /**
     * @return array
     */
    public static function getCallColumns(): array
    {
        return [
            'created_at' => [
                'name' => Yii::t('app', 'Created'),
                'type' => 'dateRange',
                'default' => false,
                'label' => 'Created',
                'function' => 'getCreatedAt',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'name' => [
                'name' => Yii::t('app', 'Name'),
                'type' => 'text',
                'default' => true,
                'label' => 'Name',
                'function' => 'getName',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'barcode' => [
                'name' => Yii::t('app', 'Barcode'),
                'type' => 'text',
                'default' => true,
                'label' => 'Barcode',
                'function' => 'getBarcode',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'count' => [
                'name' => Yii::t('app', 'Count'),
                'type' => 'text',
                'default' => true,
                'label' => 'Count',
                'function' => 'getCount',
                'headerOptions' => [
                    'width' => '50px',
                ]
            ],
            'price' => [
                'name' => Yii::t('app', 'Price'),
                'type' => 'text',
                'default' => true,
                'label' => 'Price',
                'function' => 'getPrice',
                'headerOptions' => [
                    'width' => '50px',
                ]
            ],
            'accessed_price' => [
                'name' => Yii::t('app', 'Accessed Price'),
                'type' => 'text',
                'default' => true,
                'label' => 'Accessed Price',
                'function' => 'getAccessedPrice',
                'headerOptions' => [
                    'width' => '50px',
                ]
            ],
            'weight' => [
                'name' => Yii::t('app', 'Weight'),
                'type' => 'text',
                'default' => true,
                'label' => 'Weight',
                'function' => 'getWeight',
                'headerOptions' => [
                    'width' => '50px',
                ]
            ],
            'shop_id' => [
                'name' => Yii::t('app', 'Shop'),
                'label' => 'Shop',
                'type' => 'dropdown',
                'function' => 'getShopName',
                'default' => true,
                'list' => Yii::$app->user->identity->getAllowedShops(),
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'headerOptions' => [
                    'width' => '300px',
                ],
            ]
        ];
    }

    /**
     * Список полей для поиска
     * @param Product $searchModel
     * @return array
     */
    public function getSearchColumns(Product $searchModel)
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
     * Список полей для экспорта
     * @param Product $searchModel
     * @return array
     */
    public function getExportColumns(Product $searchModel)
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
}
