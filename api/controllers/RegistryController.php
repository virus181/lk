<?php

namespace app\api\controllers;

use app\api;
use app\models\Courier;
use app\models\search\LabelSearch;
use app\models\User;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\Request;

class RegistryController extends api\base\BaseActiveController
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

        $query = Courier::find();

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $warehouseIds = $user->getAllowedWarehouseIds();
        }

        if ($warehouseIds !== false && !empty($warehouseIds)) {
            $query->andWhere(['in', 'courier.warehouse_id', $warehouseIds]);
        }

        $query->andWhere(['>=', 'courier.pickup_date', time()]);

        $query->orderBy(['courier.pickup_date' => SORT_DESC]);

        $registries = $query->all();
        $arrayDataProvider = new ArrayDataProvider([
            'allModels' => $registries,
            'pagination' => [
                'class' => Pagination::className(),
                'pageSizeLimit' => [1, count($registries)],
                'pageSize' => count($registries),
            ],
        ]);
        return $arrayDataProvider;
    }

}