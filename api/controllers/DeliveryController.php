<?php
namespace app\api\controllers;

use app\api\base\BaseActiveController;
use app\components\Clients\Dadata;
use app\delivery\Deliveries;
use app\models\Address;
use app\models\Common\Calculator;
use app\models\Delivery;
use app\models\Order;
use app\models\OrderDelivery;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\Request;
use yii\web\Response;

class DeliveryController extends BaseActiveController
{
    public $modelClass = 'app\models\OrderDelivery';

    /**
     * @return array
     */
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

    /**
     * @deprecated
     * @return Order|ArrayDataProvider
     */
    public function actionCalculate_v2_old()
    {

        /** @var Request $request */
        $request = Yii::$app->request;
        $order = new Order();
        $order->setScenario(Order::SCENARIO_CALCULATE_API);
        $order->load($request->get(), '');
        $order->validate();
        $getAllModel = $request->get('all', false);
        if ($order->hasErrors() === false) {

            $order->address = new Address();
            $order->address->city = $order->city;
            if ($order->city_fias_id) {
                $order->address->city_fias_id = $order->city_fias_id;
            } else {
                $address = (new Dadata())->getCity($order->city);
                $order->address->city_fias_id = !empty($address) ? $address['data']['fias_id'] : null;
            }

            $orderDeliveryModel = new OrderDelivery();
            $orderDeliveryModel->setScenario(OrderDelivery::SCENARIO_CALCULATE_API);

            /** @var Deliveries $deliveries */
            $deliveries = Yii::createObject(Deliveries::className(), [
                $order,
                $orderDeliveryModel
            ]);

            $orderDeliveries = $deliveries->calculate();
            $attributes = array_keys((new OrderDelivery())->getAttributes());
            $arrayDataProvider = new ArrayDataProvider([
                'allModels' => $orderDeliveries,
                'sort' => [
                    'attributes' => $attributes,
                ],
                'pagination' => [
                    'class' => Pagination::className(),
                    'pageSizeLimit' => [1, (($getAllModel) ? count($orderDeliveries) : 50)],
                    'pageSize' => count($orderDeliveries),
                ],
            ]);

            return $arrayDataProvider;
        } else {
            return $order;
        }
    }

    /**
     * @return array|ArrayDataProvider
     */
    public function actionCalculate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var Request $request */
        $request = Yii::$app->request;
        $calculator = new Calculator();
        $calculator->load($request->get(), '');
        if ($calculator->load($request->get(), '')
            && $calculator->prepareData()
            && $calculator->validate()
        ) {

            $orderDeliveryModel = new OrderDelivery();
            $orderDeliveryModel->setScenario(OrderDelivery::SCENARIO_CALCULATE_API);

            /** @var \app\delivery\Delivery $deliveries */
            $deliveries = Yii::createObject(\app\delivery\Delivery::className(), [
                $calculator,
                $orderDeliveryModel
            ]);

            $orderDeliveries = Yii::$app->cache->getOrSet(
                $calculator->getCacheParameters(),
                function () use ($deliveries)
                {
                    $calculatedDeliveries = $deliveries->calculate();
                    return empty($calculatedDeliveries) ? [] : $calculatedDeliveries;
                },
                3600
            );

            $attributes = array_keys((new OrderDelivery())->getAttributes());
            $arrayDataProvider = new ArrayDataProvider([
                'allModels' => $orderDeliveries,
                'sort' => [
                    'attributes' => $attributes,
                ],
                'pagination' => [
                    'class' => Pagination::className(),
                    'pageSizeLimit' => [1, count($orderDeliveries)],
                    'pageSize' => count($orderDeliveries),
                ],
            ]);

            return $arrayDataProvider;

        } else {
            return [
                'errors' => $calculator->errors
            ];
        }

    }

    /**
     * @return ArrayDataProvider
     */
    public function actionList()
    {
        $deliveries = Delivery::find()->all();
        $attributes = array_keys((new Delivery())->getAttributes());
        $arrayDataProvider = new ArrayDataProvider([
            'allModels' => $deliveries,
            'sort' => [
                'attributes' => $attributes,
            ],
            'pagination' => [
                'class' => Pagination::className(),
                'pageSizeLimit' => [1, count($deliveries)],
                'pageSize' => count($deliveries),
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
        $verbs['calculate'] = ['GET'];

        return $verbs;
    }
}