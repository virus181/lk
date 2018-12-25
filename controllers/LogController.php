<?php

namespace app\controllers;

use app\models\Delivery;
use app\models\Log;
use app\models\Order;
use app\models\search\ShopSearch;
use app\models\Shop;
use app\models\traits\FindModelWithCheckAccessTrait;
use app\models\User;
use app\models\Warehouse;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * LogController implements the CRUD actions for Shop model.
 */
class LogController extends Controller
{
    use FindModelWithCheckAccessTrait;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function init()
    {
        $this->modelName = Log::className();
        parent::init();
    }

    /**
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new Log();
        $order = Order::find()->where(['id' => Yii::$app->request->get('owner_id')])->one();

        $ownerIds = [$order->id];
//        if ($order->delivery) {
//            $ownerIds[] = $order->delivery->id;
//        }
//        if ($order->address) {
//            $ownerIds[] = $order->address->id;
//        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $ownerIds);


        return $this->renderAjax('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'order' => $order
        ]);
    }

}
