<?php

namespace app\models\search;

use app\models\Call;
use app\models\queries\ShopQuery;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\grid\CheckboxColumn;

/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 */
class UserSearch extends User implements SearchModelInterface
{
    const DATE_FORMAT = 'd.m.Y, H:i';
    const CLASS_NAME = 'UserSearch';

    private $cookieKey = 'userUserColumns';

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
            [['id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['auth_key', 'password_hash', 'password_reset_token', 'email', 'fio'], 'safe'],
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
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        $query->andFilterWhere([
            'user.id' => $this->id,
            'user.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'user.email', $this->email])
            ->andFilterWhere(['like', 'user.fio', $this->fio]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $shopIds = $user->getAllowedShopIds();

        if ($shopIds !== []) {
            $query->innerJoinWith(['shops' => function (ShopQuery $query) use ($shopIds) {
                $query->where(['shop.id' => $shopIds]);
            }]);
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
            ->select('user.*')
            ->from('user');

        $this->load($params);

        $query->andFilterWhere([
            'user.id' => $this->id,
            'user.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'user.email', $this->email])
            ->andFilterWhere(['like', 'user.fio', $this->fio]);

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $shopIds = $user->getAllowedShopIds();

        if ($shopIds !== []) {
            $query->leftJoin('user_shop', 'user_shop.user_id = user.id');
            $query->andFilterWhere(['user_shop.shop_id' => $shopIds]);
            $query->groupBy(['user.id']);
        }

        $models = $query->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
        ]);

        return $dataProvider;
    }

    /**
     * Список полей для поиска
     * @param User $searchModel
     * @return array
     */
    public function getSearchColumns(User $searchModel)
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
     * @param User $searchModel
     * @return array
     */
    public function getExportColumns(User $searchModel)
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
                    'width' => '200px',
                ]
            ],
            'status' => [
                'name' => Yii::t('app', 'Status'),
                'type' => 'dropdown',
                'default' => true,
                'list' => User::getStatusList(),
                'label' => 'Status',
                'function' => 'getStatus',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'email' => [
                'name' => Yii::t('app', 'Email'),
                'type' => 'text',
                'default' => true,
                'label' => 'Email',
                'function' => 'getEmail',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
            'fio' => [
                'name' => Yii::t('app', 'Fio'),
                'type' => 'text',
                'default' => true,
                'label' => 'Fio',
                'function' => 'getFio',
                'headerOptions' => [
                    'width' => '200px',
                ]
            ],
        ];
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
            default:
                $text = isset ($model[$column]) ? (string) $model[$column]: '';
        }
        return (string) $text;
    }
}
