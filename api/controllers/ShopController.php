<?php

namespace app\api\controllers;

use app\api\base\BaseActiveController;
use app\api\view\Shop\Phones;
use app\delivery\Deliveries;
use app\models\Address;
use app\models\Shop;
use app\models\OrderDelivery;
use app\models\Product;
use app\models\User;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\Request;

class ShopController extends BaseActiveController
{
    public $modelClass = 'app\models\OrderDelivery';

    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }

    public function actionList()
    {
        /** @var Request $request */
        $request = Yii::$app->request;

        $query = Shop::find();
        if ($request->get('shop_name')) {
            $query->andWhere(['LIKE', 'name', $request->get('shop_name')]);
        }

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $shopIds = $user->getAllowedShopIds();
        }

        if ($shopIds !== false && !empty($shopIds)) {
            $query->andWhere(['IN', 'order.shop_id', $shopIds]);
        }
        
        $shops = $query->all();
        $attributes = array_keys((new Shop())->getAttributes());
        $arrayDataProvider = new ArrayDataProvider([
            'allModels' => $shops,
            'sort' => [
                'attributes' => $attributes,
            ],
            'pagination' => [
                'class' => Pagination::className(),
                'pageSizeLimit' => [1, count($shops)],
                'pageSize' => count($shops),
            ],
        ]);
        return $arrayDataProvider;

    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        $verbs = parent::verbs();
        $verbs['list'] = ['GET'];

        return $verbs;
    }

    /**
     * Получение телефонии магазина
     * @return array
     */
    public function actionPhones(): array
    {
        $params = isset(Yii::$app->request->get()['include']) ? explode(',', Yii::$app->request->get()['include']) : [];

        $shops = Shop::find()->where([
            'status' => Shop::STATUS_ACTIVE
        ])->all();

        return (new Phones())->setShops($shops)->setIncludeParams($params)->build();
    }
}