<?php

namespace app\api\controllers;

use app\api;
use app\models\search\LabelSearch;
use app\models\User;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\Request;

class LabelController extends api\base\BaseActiveController
{
    public $modelClass = 'app\models\Label';

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

        $query = LabelSearch::find();

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $shopIds = $user->getAllowedShopIds();
        }

        $query->andWhere(['in', 'order.shop_order_number', explode(',', $request->get('order_id'))]);
        if (!empty($shopIds)) {
            $query->andWhere(['in', 'order.shop_id', $shopIds]);
        }

        $labels = $query->all();
        $arrayDataProvider = new ArrayDataProvider([
            'allModels' => $labels,
            'pagination' => [
                'class' => Pagination::className(),
                'pageSizeLimit' => [1, count($labels)],
                'pageSize' => count($labels),
            ],
        ]);
        return $arrayDataProvider;
    }

}