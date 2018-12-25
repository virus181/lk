<?php
namespace app\controllers;

use app\models\Delivery;
use app\models\Helper;
use app\models\Order;
use app\models\search\OrderSearch;
use app\models\User;
use app\models\Warehouse;
use Yii;
use app\models\Courier;
use app\models\search\CourierSearch;
use app\components\UserException;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;
use yii\web\Response;
use yii\db\Query;
use yii\helpers\Url;

class CourierController extends Controller
{
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

    /**
     * Lists all Registry models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CourierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $exportProvider = $searchModel->export(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'exportProvider' => $exportProvider,
        ]);
    }

    /**
     * Скачивание файла реестра
     *
     * @return mixed
     * @throws UserException
     */
    public function actionDownload($id)
    {
        if (!$courier = Courier::find()->where(['id' => $id])->one()) {
            throw new UserException(404, Yii::t('app', 'Not found'));
        }

        $content = $this->renderPartial('_registry', [
            'courier' => $courier
        ]);

        $pdf = new Pdf([
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_DOWNLOAD,
            'cssFile' => '@app/web/css/registry.css',
            'filename' => sprintf('registry_%d_%s.pdf', $courier->id, date('d_m_Y', time())),
            'content' => $content
        ]);
        return $pdf->render();
    }

    /**
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Courier();

        if ($model->load(Yii::$app->request->post())) {
            $model->pickup_date = strtotime($model->pickup_date);
            if ($model->validate() && $model->save()) {
                return $this->redirect(['index']);
            }
        } else {
            $model->pickup_date = date('d.m.Y', time() + 86400);
            $carriers = ArrayHelper::map(
                Delivery::find()->asArray()->all(),
                'carrier_key',
                'name'
            );
            $warehouse = ArrayHelper::map(
                Warehouse::find()->where(['>', 'status', Helper::STATUS_BLOCKED])->asArray()->all(),
                'id',
                'name'
            );
            return $this->renderAjax('create', [
                'carrierKeys' => $carriers,
                'model' => $model,
                'warehouses' => $warehouse
            ]);
        }
    }

    /**
     * Updates an existing Registry model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Получение списка заказов
     * @return string
     * @throws UserException
     */
    public function actionOrderList()
    {
        if ($courierId = Yii::$app->request->post('courierId')) {

            /** @var User $user */
            if ($user = Yii::$app->user->identity) {
                $shopIds = $user->getAllowedShopIds();
            }

            $carrier = Courier::find()->where('id = ' . $courierId)->one();
            $searchModel = new OrderSearch();

            $query = Order::find()
                ->joinWith(['delivery', 'shop', 'courier'])
                ->andWhere('order.status = "' . (new Order())->getWorkflowStatusId(Order::STATUS_READY_FOR_DELIVERY).'"')
                ->andWhere('order.courier_id IS NULL')
                ->andWhere('order_delivery.carrier_key = "' . $carrier->carrier_key . '"');

            if ($shopIds === false || count($shopIds)) {
                $query->andWhere(['IN', 'order.shop_id', $shopIds]);
            }

            $query->limit(50);

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort'=> ['defaultOrder' => ['created_at' => SORT_DESC]],
                'pagination' => false
            ]);

            return $this->renderAjax('_orders', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'courierId' => $courierId
            ]);
        } else {
            if(!$courierId) {
                throw new UserException(400, Yii::t('app', 'Missing Courier ID'));
            }
        }
    }

    /**
     * Добавление заказа в реестр
     * @return array
     * @throws UserException
     */
    public function actionAddOrders()
    {
        if ($orderIds = Yii::$app->request->post('selection')) {

            if(!$courierId = Yii::$app->request->post('id')) {
                throw new UserException(400, Yii::t('app', 'Missing courier Id'));
            }

            $courier = Courier::find()->where('id='.$courierId)->one();

            $orders = Order::find()->where(['IN', 'order.id', $orderIds])->all();
            /** @var Order $order */
            foreach ($orders as $order) {
                try {
                    if ($order->status == (new Order())->getWorkflowStatusId(Order::STATUS_READY_FOR_DELIVERY)
                        && !$order->courier_id
                        && $order->delivery->carrier_key == $courier->carrier_key
                        && $courier->pickup_date >= strtotime(date('Y-m-d', time()))
                        && $order->sendToStatus(Order::STATUS_WAITING_COURIER)
                    ) {
                        (new Query())->createCommand()->update('{{%order}}', [
                            'courier_id' => $courier->id,
                            'status' => $order->status
                        ], ['id' => $order->id])->execute();
//                        $order->courier_id = $courier->id;
//                        $order->status = (new Order())->getWorkflowStatusId(Order::STATUS_WAITING_COURIER);
//                        $order->save();
                    } else {
                        throw new UserException(422, Yii::t('app', 'Order can not be added to registry', [$order->id]));
                    }
                } catch (Exception $e) {
                    throw new UserException(422, Yii::t('app', 'Order can not be added to registry', [$order->id]));
                }
            }

            Yii::$app->session->addFlash('success', Yii::t('app', 'Orders was added to registry'));

            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['url' => Url::to(['courier/index'])];
        } else {
            throw new UserException(400, Yii::t('app', 'Missing Order IDs'));
        }
    }

    /**
     * Deletes an existing Registry model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Registry model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Courier the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Courier::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
