<?php

namespace app\api\controllers;

use app\api\base\BaseActiveController;
use app\delivery\Deliveries;
use app\models\Address;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Product;
use app\models\User;
use app\models\Warehouse;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\Request;

class WarehouseController extends BaseActiveController
{
    public $modelClass = 'app\models\Warehouse';

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
        $warehouse = new Warehouse();
        $warehouse->setScenario(Warehouse::SCENARIO_WAREHOUSE_LIST_API);
        $warehouse->load($request->get(), '');

        $query = Warehouse::find();

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $warehouseIds = $user->getAllowedWarehouseIds();
        }

        if (!empty($warehouseIds)) {
            $query->andWhere(['in', 'warehouse.id', $warehouseIds]);
        }

        $warehouses = $query->all();
        $attributes = array_keys((new Warehouse())->getAttributes());
        $arrayDataProvider = new ArrayDataProvider([
            'allModels' => $warehouses,
            'sort' => [
                'attributes' => $attributes,
            ],
            'pagination' => [
                'class' => Pagination::className(),
                'pageSizeLimit' => [1, count($warehouses)],
                'pageSize' => count($warehouses),
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
}