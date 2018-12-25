<?php

namespace app\models\search;

use app\models\Shop;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\grid\CheckboxColumn;

/**
 * ShopSearch represents the model behind the search form of `app\models\Shop`.
 */
class ShopSearch extends Shop implements SearchModelInterface
{
    const DATE_FORMAT = 'd.m.Y, H:i';
    const CLASS_NAME = 'ShopSearch';

    public $default_warehouse;

    private $cookieKey = 'userShopColumns';

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
            [['id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['name', 'url', 'default_warehouse'], 'safe'],
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
        $query = Shop::find()->joinWith(['defaultWarehouse']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if ($this->status == null) {
            $query->andFilterWhere([
                'shop.status' => 10
            ]);

        }

        $query->andFilterWhere([
            'shop.id' => $this->id,
            'shop.status' => $this->status,
            'shop.created_at' => $this->created_at,
            'shop.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'shop.name', $this->name])
            ->andFilterWhere(['like', 'shop.url', $this->url])
            ->andFilterWhere(['like', 'warehouse.name', $this->default_warehouse]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['shop.id' => $user->getAllowedShopIds()]);

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
                    warehouse.name as default_warehouse, 
                    shop.*')
            ->from('shop')
            ->leftJoin('warehouse', 'shop.default_warehouse_id = warehouse.id');

        $this->load($params);

        $query->andFilterWhere([
            'shop.id' => $this->id,
            'shop.status' => $this->status,
            'shop.created_at' => $this->created_at,
            'shop.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'shop.name', $this->name])
            ->andFilterWhere(['like', 'shop.url', $this->url])
            ->andFilterWhere(['like', 'warehouse.name', $this->default_warehouse]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['shop.id' => $user->getAllowedShopIds()]);

        $models = $query->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
        ]);

        return $dataProvider;
    }

    /**
     * Список полей для поиска
     * @param Shop $searchModel
     * @return array
     */
    public function getSearchColumns(Shop $searchModel)
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
                $text = Shop::getStatuses()[$model[$column]];
                break;
            default:
                $text = isset ($model[$column]) ? (string) $model[$column]: '';
        }
        return (string) $text;
    }

    /**
     * Список полей для экспорта
     * @param Shop $searchModel
     * @return array
     */
    public function getExportColumns(Shop $searchModel)
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
                'function' => 'getId',
                'headerOptions' => [
                    'width' => '50px',
                ]
            ],
            'status' => [
                'name' => Yii::t('app', 'Status'),
                'type' => 'dropdown',
                'default' => true,
                'list' => Shop::getStatusList(),
                'label' => 'Status',
                'function' => 'getStatus',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'name' => [
                'name' => Yii::t('app', 'Shop name'),
                'type' => 'text',
                'default' => true,
                'label' => 'Shop name',
                'function' => 'getName',
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
            'default_warehouse' => [
                'name' => Yii::t('app', 'Default Warehouse'),
                'type' => 'text',
                'default' => true,
                'label' => 'Default Warehouse',
                'function' => 'getDefaultWarehouseName',
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
            'url' => [
                'name' => Yii::t('app', 'Url'),
                'type' => 'text',
                'default' => true,
                'label' => 'Url',
                'function' => 'getUrl',
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
        ];
    }
}
