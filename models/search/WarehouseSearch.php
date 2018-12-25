<?php

namespace app\models\search;

use app\models\User;
use app\models\Warehouse;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\grid\CheckboxColumn;

/**
 * WarehouseSearch represents the model behind the search form of `app\models\Warehouse`.
 */
class WarehouseSearch extends Warehouse
{
    const DATE_FORMAT = 'd.m.Y, H:i';
    const CLASS_NAME = 'WarehouseSearch';

    public $address;

    private $cookieKey = 'userWarehouseColumns';

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
            [['id', 'status'], 'integer'],
            [['name', 'contact_fio', 'contact_phone', 'address'], 'safe'],
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
        $query = Warehouse::find()->joinWith('address');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        $query->andFilterWhere([
            'warehouse.id' => $this->id,
            'warehouse.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'contact_fio', $this->contact_fio])
            ->andFilterWhere(['like', 'contact_phone', $this->contact_phone])
            ->andFilterWhere(['like', 'address.full_address', $this->address]);

        $dataProvider->sort->attributes = array_merge($dataProvider->sort->attributes, [
            'address' => [
                'asc' => [
                    'address.full_address' => SORT_ASC
                ],
                'desc' => [
                    'address.full_address' => SORT_DESC
                ],
                'label' => Yii::t('app', 'Address'),
            ],
        ]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        if ($user->getAllowedShopIds() !== []) {
            $query->andFilterWhere(['warehouse.id' => $user->getAllowedWarehouseIds()]);
        }

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
            ->select('address.full_address as address, 
                    warehouse.*')
            ->from('warehouse')
            ->leftJoin('address', 'warehouse.address_id = address.id');

        $this->load($params);

        $query->andFilterWhere([
            'warehouse.id' => $this->id,
            'warehouse.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'contact_fio', $this->contact_fio])
            ->andFilterWhere(['like', 'contact_phone', $this->contact_phone])
            ->andFilterWhere(['like', 'address.full_address', $this->address]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        if ($user->getAllowedShopIds() !== []) {
            $query->andFilterWhere(['warehouse.id' => $user->getAllowedWarehouseIds()]);
        }

        $models = $query->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
        ]);

        return $dataProvider;
    }

    /**
     * Список полей для поиска
     * @param Warehouse $searchModel
     * @return array
     */
    public function getSearchColumns(Warehouse $searchModel)
    {
        $result[] = [
            'class' => CheckboxColumn::className(),
            'headerOptions' => [
                'width' => '40px',
                'data-resizable-column-id' => 'checker'
            ],
        ];
        $commonSearch = new CommonSearch();
        foreach ($commonSearch->getUserColumns(self::getWarehouseColumns(), $this->cookieKey) as $column => $data) {
            if ($data) {
                $result[] = $commonSearch->getColumn($column, self::getWarehouseColumns()[$column], $searchModel);
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
            case 'created_at':
                $text = date(self::DATE_FORMAT, strtotime($model['ring_time']));
                break;
            case 'status':
                $text = WarehouseSearch::getStatuses()[$model[$column]];
                break;
            default:
                $text = isset ($model[$column]) ? (string) $model[$column]: '';
        }
        return (string) $text;
    }

    /**
     * Список полей для экспорта
     * @param Warehouse $searchModel
     * @return array
     */
    public function getExportColumns(Warehouse $searchModel)
    {
        $result = [];
        $commonSearch = new CommonSearch();
        foreach ($commonSearch->getUserColumns(self::getWarehouseColumns(), $this->cookieKey) as $column => $data) {
            if ($data) {
                $result[] = $commonSearch->getColumn($column, self::getWarehouseColumns()[$column], $searchModel, true, false, self::CLASS_NAME);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getWarehouseColumns(): array
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
                'label' => 'Status',
                'list' => Warehouse::getStatusList(),
                'function' => 'getStatus',
                'headerOptions' => [
                    'width' => '100px',
                ]
            ],
            'name' => [
                'name' => Yii::t('app', 'Warehouse Name'),
                'type' => 'text',
                'default' => true,
                'label' => 'Warehouse Name',
                'function' => 'getWarehouseName',
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
            'contact_fio' => [
                'name' => Yii::t('app', 'Contact Fio'),
                'type' => 'text',
                'default' => true,
                'label' => 'Contact Fio',
                'function' => 'getContactFio',
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
            'contact_phone' => [
                'name' => Yii::t('app', 'Contact Phone'),
                'type' => 'text',
                'default' => true,
                'label' => 'Contact Phone',
                'function' => 'getContactPhone',
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
            'address' => [
                'name' => Yii::t('app', 'Address'),
                'type' => 'text',
                'default' => true,
                'label' => 'Address',
                'function' => 'getFullAddress',
                'headerOptions' => [
                    'width' => '150px',
                ]
            ],
        ];
    }
}
