<?php
namespace app\models\search;

use app\models\Call;
use app\models\Helper\Phone;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use app\widgets\grid\CheckboxColumn;
use yii\data\ArrayDataProvider;
use yii\db\Query;

class CallSearch extends Call implements SearchModelInterface
{
    const DATE_FORMAT = 'd.m.Y, H:i';
    const CLASS_NAME = 'CallSearch';

    const TAG_NO_RECORD = 8;

    public $default_warehouse;
    public $call_status;
    public $client_fio;
    public $call_time;

    private $cookieKey = 'userCallColumns';

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
            [['order_id', 'created_at', 'updated_at', 'shop_id', 'id', 'tag'], 'integer'],
            [['shop_phone', 'client_phone', 'operator_name', 'direction', 'client_fio', 'note', 'call_time'], 'string'],
            [['name', 'url', 'default_warehouse', 'call_status'], 'safe'],
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
        $query = Call::find()->joinWith(['shop', 'order']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        $query->andFilterWhere([
            'call.order_id' => $this->order_id,
            'call.shop_id' => $this->shop_id,
            'call.tag' => $this->tag,
            'call.direction' => $this->direction,
        ]);

        $query->andFilterWhere([
            'like', 'call.operator_name', $this->operator_name
        ]);

        $query->andFilterWhere([
            'like', 'order.fio', $this->client_fio,
        ]);

        $query->andFilterWhere([
            'like', 'call.note', $this->note,
        ]);

        if ($this->client_phone) {
            $query->andFilterWhere([
                'like', 'call.client_phone', (new Phone($this->client_phone))->getClearPhone()
            ]);
        }

        if ($this->shop_phone) {
            $query->andFilterWhere([
                'like', 'call.shop_phone', (new Phone($this->shop_phone))->getClearPhone()
            ]);
        }

        if ($this->call_status != '' && $this->call_status == 1) {
            $query->andWhere([
                'not', ['call.rec_uid' => null]
            ]);
        } elseif ($this->call_status != '' && $this->call_status == 0) {
            $query->andWhere(['call.rec_uid' => null]);
        }

        if($this->created_at) {
            $dates = explode('-', $this->created_at);
            $query->andFilterWhere([
                '>', 'call.ring_time', date(DATE_ATOM, strtotime(trim($dates[0])))
            ]);
            $query->andFilterWhere([
                '<', 'call.ring_time', date(DATE_ATOM, strtotime(trim($dates[1])) + 86400)
            ]);
        }

        $query->andWhere([
            'not', ['call.tag' => self::TAG_NO_RECORD]
        ]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $query->andFilterWhere(['shop.id' => $user->getAllowedShopIds()]);

        $query->orderBy(['call.call_id' => SORT_DESC]);

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
                    order.fio as client_fio, 
                    shop.name as shop_name, 
                    call.*')
            ->from('call')
            ->leftJoin('order', 'call.order_id = order.id')
            ->leftJoin('shop', 'shop.id = call.shop_id');

        $this->load($params);

        $query->andFilterWhere([
            'call.order_id' => $this->order_id,
            'call.direction' => $this->direction,
            'call.tag' => $this->tag,
        ]);

        $query
            ->andFilterWhere(['like', 'order.fio', $this->client_fio])
            ->andFilterWhere(['like', 'call.client_phone', $this->client_phone])
            ->andFilterWhere(['like', 'call.shop_phone', $this->shop_phone]);


        if($this->created_at) {
            $dates = explode('-', $this->created_at);
            $query->andFilterWhere([
                '>', 'call.ring_time', date(DATE_ATOM, strtotime(trim($dates[0])))
            ]);
            $query->andFilterWhere([
                '<', 'call.ring_time', date(DATE_ATOM, strtotime(trim($dates[1])) + 86400)
            ]);
        }

        $models = $query->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
        ]);

        return $dataProvider;
    }

    /**
     * Список полей для поиска
     * @param Call $searchModel
     * @return array
     */
    public function getSearchColumns(Call $searchModel)
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
     * @param $model
     * @param bool $asLink
     * @return string
     */
    public function getExportColumnContent(string $column, array $data, $model, $asLink = true): string
    {
        switch ($column) {
            case 'created_at':
                $text = date(self::DATE_FORMAT, strtotime($model['ring_time']));
                break;
            case 'tag':
                $text = Call::getTagList()[$model[$column]];
                break;
            case 'direction':
                $text = isset(Call::getDirectionList()[$model[$column]]) ? Call::getDirectionList()[$model[$column]] : '';
                break;
            case 'shop_id':
                $text = $model['shop_name'];
                break;
            case 'download':
                $text = sprintf(self::DOWNLOAD_URL, $model['key']);
                break;
            default:
                $text = isset ($model[$column]) ? (string) $model[$column]: '';
        }
        return (string) $text;
    }

    /**
     * Список полей для экспорта
     * @param Call $searchModel
     * @return array
     */
    public function getExportColumns(Call $searchModel)
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
            'created_at' => [
                'name' => Yii::t('app', 'Ring date'),
                'type' => 'dateRange',
                'default' => true,
                'label' => 'Ring date',
                'function' => 'getRingDate',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'client_fio' => [
                'name' => Yii::t('app', 'Client name'),
                'label' => 'Client name',
                'default' => true,
                'type' => 'text',
                'function' => 'getClientName',
                'headerOptions' => [
                    'width' => '150px',
                ],
            ],
            'client_phone' => [
                'name' => Yii::t('app', 'Client phone'),
                'label' => 'Client phone',
                'type' => 'text',
                'function' => 'getClientPhone',
                'default' => true,
                'headerOptions' => [
                    'width' => '250px',
                ],
            ],
            'order_id' => [
                'name' => Yii::t('app', 'Fastery number'),
                'label' => 'Fastery number',
                'default' => true,
                'type' => 'text',
                'function' => 'getOrderId',
                'headerOptions' => [
                    'width' => '50px',
                ],
            ],
            'operator_name' => [
                'name' => Yii::t('app', 'Operator name'),
                'label' => 'Operator name',
                'type' => 'text',
                'default' => true,
                'function' => 'getOperatorName',
                'headerOptions' => [
                    'width' => '400px',
                ],
            ],

            'shop_phone' => [
                'name' => Yii::t('app', 'Shop phone'),
                'label' => 'Shop phone',
                'type' => 'text',
                'function' => 'getShopPhone',
                'default' => false,
                'headerOptions' => [
                    'width' => '250px',
                ],
            ],
            'tag' => [
                'name' => Yii::t('app', 'Label'),
                'label' => 'Label',
                'type' => 'dropdown',
                'function' => 'getTag',
                'list' => Call::getTagList(),
                'default' => true,
                'headerOptions' => [
                    'width' => '150px',
                ],
            ],
            'direction' => [
                'name' => Yii::t('app', 'Direction'),
                'label' => 'Direction',
                'type' => 'dropdown',
                'default' => true,
                'function' => 'getDirection',
                'list' => Call::getDirectionList(),
                'headerOptions' => [
                    'width' => '150px',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],
            ],
            'call_status' => [
                'name' => Yii::t('app', 'Status Call'),
                'label' => 'Status Call',
                'type' => 'dropdown',
                'default' => false,
                'function' => 'getStatusCall',
                'list' => Call::CALL_STATUS_LIST,
                'headerOptions' => [
                    'width' => '50px',
                ],
                'filterOptions' => [
                    'style' => 'position: relative;',
                ],
                'contentOptions' => [
                    'class' => 'frr',
                    'style' => 'overflow: hidden',
                ],
            ],
            'call_time' => [
                'name' => Yii::t('call', 'Call time'),
                'label' => 'Call time',
                'type' => 'text',
                'default' => true,
                'function' => 'getCallTime',
                'headerOptions' => [
                    'width' => '100px',
                ],
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
            ],
            'note' => [
                'name' => Yii::t('app', 'Note'),
                'label' => 'Note',
                'type' => 'text',
                'function' => 'getNote',
                'default' => true,
                'headerOptions' => [
                    'width' => '300px',
                ],
            ],
            'download' => [
                'name' => Yii::t('app', 'Record'),
                'label' => 'Record',
                'type' => 'text',
                'function' => 'getDownloadUrl',
                'default' => true,
                'list' => Yii::$app->user->identity->getAllowedShops(),
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
