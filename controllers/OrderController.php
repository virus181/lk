<?php
namespace app\controllers;

use app\behaviors\LogBehavior;
use app\components\DbDependencyHelper;
use app\components\UserException;
use app\delivery\Deliveries;
use app\delivery\Delivery;
use app\models\Address;
use app\models\Call;
use app\models\Common\Orders;
use app\models\Courier;
use app\models\DeliveryStatus;
use app\models\ErrorException;
use app\models\forms\OrdersCourierCall;
use app\models\Helper;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\OrderMessage;
use app\models\OrderProduct;
use app\models\Product;
use app\models\search\OrderSearch;
use app\models\Shop;
use app\models\traits\FindModelWithCheckAccessTrait;
use app\models\User;
use app\models\Warehouse;
use app\workflow\WorkflowHelper;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Response;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
{
    use FindModelWithCheckAccessTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete'     => ['POST'],
                    'set-status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     *
     */
    public function init()
    {
        $this->modelName = Order::className();
        parent::init();
    }

    /**
     * Lists all Order models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel    = new OrderSearch();
        $dataProvider   = $searchModel->search(Yii::$app->request->queryParams);
        $exportProvider = $searchModel->export(Yii::$app->request->queryParams);

        Yii::$app->getDb()->cache(function () use ($dataProvider) {
            $dataProvider->prepare();
        }, Helper::MIN_CACHE_VALUE, DbDependencyHelper::generateDependency(Order::find()));

        return $this->render('index', [
            'searchModel'            => $searchModel,
            'dataProvider'           => $dataProvider,
            'exportProvider'         => $exportProvider,
            'queryParams'            => Yii::$app->request->queryParams,
            'hasDeliveryErrorOrders' => $searchModel->hasDeliveryErrorOrders(),
        ]);
    }

    /**
     * Просмотр и редактирование заказа
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $post             = Yii::$app->request->post();
        $errors           = [];
        $validateOrder    = false;
        $validateProducts = false;
        $validateDelivery = false;
        $validateAddress  = false;

        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var Order $order */
        $order = $this->findModel($id, ['with' => ['orderProducts.product']]);
        if (!in_array($order->status, Order::STATUS_PAYMENT_CHANGABLE)) {
            $order->addError('status', Yii::t('order', 'Order is not editable'));
        }

        $shops = $user->getAllowedShops();

        $warehouses = ArrayHelper::map(
            Warehouse::find()
                ->joinWith(['address'])
                ->andFilterWhere([
                    'warehouse.id' => $user->getAllowedWarehouseIds()
                ])
                ->asArray()
                ->all(),
            'id',
            'address.full_address'
        );
        /** @var Product[] $products */
        $products = [];

        if ($order->address === null) {
            $order->address = new Address();
        }

        if (!$order->hasErrors() && $order->load($post)) {
            $order->address->load($post);
            $validateAddress = $order->address->validate();

            foreach (Yii::$app->request->post('Product', []) as $i => $productRequest) {
                if ($productRequest['id'] != '') {
                    $products[$i] = Product::findOne($productRequest['id']);
                } else {
                    $products[$i] = new Product();
                }
                $products[$i]->shop_id = $order->shop_id;
            }
            Product::loadMultiple($products, $post);
            foreach ($products as $product) {
                if ($product->weight !== '') {
                    $product->weight = str_replace(',', '.', $product->weight) * 1000;
                }
            }

            $commonOrder      = new \app\models\Common\Order($order);
            $validateProducts = Product::validateMultiple($products)
                && $commonOrder->validateConsistentlyProducts($products);
            if (!empty($products)) {
                $order->products = $products;
            }

            if (Yii::$app->request->post('OrderDelivery', [])) {
                if ($order->delivery === null) {
                    $order->delivery = new OrderDelivery();
                }
                $order->delivery->load($post);
                $order->partial = $order->delivery->partial;

                if ($order->delivery->loadDelivery($order, Yii::$app->request->post('OrderDelivery'))) {
                    $validateDelivery = $order->delivery->validate();
                } else {
                    $validateDelivery = false;
                }

                if ($order->delivery->type == OrderDelivery::DELIVERY_TO_DOOR) {
                    $order->address->scenario = Address::SCENARIO_ADDRESS_FULL;
                } else {
                    $order->address->scenario = Address::SCENARIO_ADDRESS_TO_CITY;
                }

                $validateAddress = $order->address->validate();

            } else {
                $order->delivery  = null;
                $validateDelivery = true;
            }

            $validateOrder = $order->validate();
        }

        if ($order->products === []) {
            $order->products = [new Product()];
        }

        if ($validateProducts
            && $validateAddress
            && $validateDelivery
            && $validateOrder
            && $order->save()
        ) {
            Yii::$app->session->addFlash('success', Yii::t('app', 'Order has ben updated'));
            return $this->refresh();
        } else {
            if (!in_array($order->status, Order::STATUS_EDITABLE)) {
                $order->disabledEdit        = !in_array($order->getWorkflowStatusKey($order->status), [
                    Order::STATUS_IN_COLLECTING,
                    Order::STATUS_CONFIRMED
                ]);
                $order->disabledProductEdit = true;
                if ($order->delivery) {
                    $order->delivery->disabledEdit = true;
                }
            }

            if ($post) {
                if (!$validateDelivery) {
                    if (!$order->delivery) {
                        Yii::$app->session->addFlash('danger', Yii::t('app', 'Delivery cannot be blank'));
                    } else {
                        if ($order->delivery->hasErrors()) {
                            $errorMessage = [];
                            foreach ($order->delivery->errors as $error) {
                                $errorMessage[] = $error[0];
                            }
                            Yii::$app->session->addFlash('danger', implode('<br />', $errorMessage));
                        }
                    }
                }
                if (!$validateProducts) {
                    foreach ($products as $product) {
                        if ($product->hasErrors()) {
                            $errorMessage = 'Ошибка заполнения товара "' . $product->name . '": ';
                            foreach ($product->errors as $error) {
                                $errorMessage .= $error[0];
                            }
                            Yii::$app->session->addFlash('danger', $errorMessage);
                        }
                    }
                }
                if (!$validateAddress || !$validateOrder) {
                    Yii::$app->session->addFlash('danger', Yii::t('app', 'Order validation error'));
                }
                if ($order->hasErrors()) {
                    $errorMessage = [];
                    foreach ($order->errors as $error) {
                        $errorMessage[] = $error[0];
                    }
                    Yii::$app->session->addFlash('danger', implode('<br />', $errorMessage));
                }
            }

            if (Yii::$app->session->get('id')
                && Yii::$app->session->get('id') == $order->id
            ) {
                Yii::$app->session->set('id', null);
                $showOrderHelper = (new Order())->getWorkflowStatusKey($order->status);
            }

            if (empty($commonOrder)) {
                $commonOrder = new \app\models\Common\Order($order);
            }

            return $this->render('create', [
                'order'              => $order,
                'shops'              => $shops,
                'warehouses'         => $warehouses,
                'showOrderHelper'    => $showOrderHelper ?? false,
                'actualDeliveryCost' => $commonOrder->getActualDeliveryCost(),
                'messages'           => $order->getOrderMessages()->all(),
                'errors'             => $errors,
                'buttons'            => [
                    'resend'         => true,
                    'status'         => ($order->status && $order->id),
                    'printLabel'     => ((bool)$order->dispatch_number),
                    'deliveryStatus' => ((bool)count($order->deliveryStatuses)),
                    'problemStatus' => (Yii::$app->user->can('/order/*')
                        && $order->status == (new Order())->getWorkflowStatusId(Order::STATUS_DELIVERY_ERROR)),
                    'actualStatus'   => ($order->id
                        && $order->statusName != Order::STATUS_CREATED
                        && Yii::$app->user->can('/order/*')),
                    'log'            => (false && Yii::$app->user->can('/log/index') && $order->id)
                ],
                'context'            => [
                    'id'               => $order->id,
                    'isPartial'        => $order->partial,
                    'isPerformAddress' => $order->shop->parse_address,
                    'isAvailableCall'  => Yii::$app->user->can('/order/shop-call')
                ]
            ]);
        }
    }

    /**
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $post              = Yii::$app->request->post();
        $event             = new \app\events\Order();
        $validateSetStatus = false;
        $valideteOrder     = false;
        $validateProducts  = false;
        $validateDelivery  = false;
        $validateAddress   = false;
        /** @var User $user */
        $user           = Yii::$app->user->identity;
        $order          = new Order();
        $order->address = new Address();
        $shops          = $user->getAllowedShops();

        $warehouses = ArrayHelper::map(
            Warehouse::find()
                ->joinWith(['address'])
                ->andFilterWhere(['warehouse.id' => $user->getAllowedWarehouseIds()])
                ->asArray()
                ->all(),
            'id',
            'address.full_address'
        );

        /** @var Product[] $products */
        $products = [new Product()];

        if ($orderLoad = $order->load($post)) {
            $order->address->load($post);
            $validateAddress = $order->address->validate();

            foreach (Yii::$app->request->post('Product', []) as $i => $productRequest) {
                if (!empty($productRequest['id'])) {
                    $products[$i] = Product::findOne($productRequest['id']);
                } else {
                    $products[$i] = new Product();
                }
                $products[$i]->scenario = Product::SCENARIO_CREATE_ORDER_LK;
                $products[$i]->shop_id  = $order->shop_id;

            }

            Product::loadMultiple($products, $post);
            foreach ($products as $product) {
                if ($product->weight !== '') {
                    $product->weight = ((float)str_replace(',', '.', $product->weight)) * 1000;
                }
            }

            $commonOrder      = new \app\models\Common\Order($order);
            $validateProducts = Product::validateMultiple($products) && $commonOrder->validateConsistentlyProducts($products);
            $order->products  = $products;

            if (Yii::$app->request->post('OrderDelivery', [])) {
                $orderDelivery = new OrderDelivery();
                $orderDelivery->load($post);
                $order->partial = $orderDelivery->partial;

                if ($orderDelivery->loadDelivery($order, Yii::$app->request->post('OrderDelivery'))) {
                    $validateDelivery = $orderDelivery->validate();
                } else {
                    $validateDelivery = false;
                }

                if ($orderDelivery->type == OrderDelivery::DELIVERY_TO_DOOR) {
                    $order->address->scenario = Address::SCENARIO_ADDRESS_FULL;
                } else {
                    $order->address->scenario = Address::SCENARIO_ADDRESS_TO_CITY;
                }

                $validateAddress = $order->address->validate();

                $order->delivery = $orderDelivery;

            } else {
                $validateDelivery = true;
            }

            $validateSetStatus = $order->sendToStatus(Order::STATUS_CREATED);
        } else {
            $order->products = $products;
        }

        if ($orderLoad) {
            $valideteOrder = $order->validate();
        }

        if ($validateProducts && $validateAddress && $validateDelivery && $validateSetStatus && $valideteOrder && $order->save()) {

            $event->setOrder($order)->setIsApi(false)->prepareEvent();
            Yii::$app->trigger(\app\events\Order::EVENT_ORDER_CREATED, $event);

            Yii::$app->session->addFlash('success', Yii::t('app', 'Order has ben created'));
            Yii::$app->session->set('id', $order->id);
            return $this->redirect(Url::to(['view', 'id' => $order->id]));
        } else {

            if ($post) {
                if (!$validateDelivery) {
                    if (!$order->delivery) {
                        Yii::$app->session->addFlash('danger', Yii::t('app', 'Delivery cannot be blank'));
                    } else {
                        if ($order->delivery->hasErrors()) {
                            $errorMessage = [];
                            foreach ($order->delivery->errors as $error) {
                                $errorMessage[] = $error[0];
                            }
                            Yii::$app->session->addFlash('danger', implode('<br />', $errorMessage));
                        }
                    }
                }
                if (!!$validateAddress || !$valideteOrder) {
                    Yii::$app->session->addFlash('danger', Yii::t('app', 'Order validation error'));
                }
                if (!$validateProducts) {
                    foreach ($products as $product) {
                        foreach ($product->errors as $error) {
                            if (count($error)) Yii::$app->session->addFlash('danger', $error[0]);
                        }
                    }
                }
                if (!$validateSetStatus) {
                    $statuses = $order->getStatuses();
                    Yii::$app->session->addFlash('danger', Yii::t('app', 'Order can not be transferred to a status {status}', [
                        'status' => $statuses[Order::STATUS_CREATED],
                    ]));
                }
            }

            return $this->render('create', [
                'order'      => $order,
                'shops'      => $shops,
                'warehouses' => $warehouses,
                'buttons'    => [],
                'context'    => [
                    'isPerformAddress' => false,
                    'isPartial'        => $order->partial,
                    'isAvailableCall'  => Yii::$app->user->can('/order/shop-call')
                ]
            ]);
        }
    }

    /**
     * @return string|Response
     */
    public function actionCourierCall()
    {
        $ordersCourierCall = new OrdersCourierCall();
        $searchModel       = new OrderSearch();

        try {
            if ($orderIds = Yii::$app->request->post('selection')) {
                $ordersCourierCall->orderIds = ArrayHelper::map(
                    $orders = Order::find()
                        ->where(['order.id' => $orderIds])
                        ->andWhere('order.provider_number IS NOT NULL')
                        ->joinWith(['delivery'])
                        ->andWhere('order_delivery.carrier_key IS NOT NULL')
                        ->all(),
                    'id',
                    'id'
                );

                if ($ordersCourierCall->orderIds == false || $ordersCourierCall->carriers == false) {
                    throw new UserException(400, Yii::t('app', 'For selected orders, a courier can not be called'));
                }

                $queryReadyDeliveryOrders = Order::find()
                    ->joinWith(['delivery'])
                    ->andWhere('order.provider_number IS NOT NULL')
                    ->andWhere('order.courier_id IS NULL')
                    ->andWhere(['order.status' => (new Order())->getWorkflowStatusId(Order::STATUS_READY_FOR_DELIVERY)])
                    ->andWhere('order_delivery.carrier_key IS NOT NULL')
                    ->andWhere(['NOT IN', 'order.id', $ordersCourierCall->orderIds])
                    ->andWhere(['order.shop_id' => $orders[0]->shop_id]);

                $dataProvider = new ActiveDataProvider([
                    'query' => $queryReadyDeliveryOrders,
                    'sort'  => ['defaultOrder' => ['created_at' => SORT_DESC]]
                ]);

                $ordersCourierCall->validate();

                return $this->renderAjax('_courierCall', [
                    'ordersCourierCall' => $ordersCourierCall,
                    'searchModel'       => $searchModel,
                    'dataProvider'      => $dataProvider
                ]);
            } elseif ($ordersCourierCall->load(Yii::$app->request->post()) && $ordersCourierCall->call()) {
                Yii::$app->session->addFlash('success', Yii::t('app', 'Couriers will be called'));
                $orders = Order::find()->where(['IN', 'id', $ordersCourierCall->orderIds])->all();
                foreach ($orders as $order) {
                    $delivery                = $order->delivery;
                    $delivery->pickup_date   = is_numeric($ordersCourierCall->pickup_date)
                        ? $ordersCourierCall->pickup_date
                        : strtotime($ordersCourierCall->pickup_date);
                    $delivery->delivery_date = $delivery->pickup_date + $delivery->min_term * 86400;
                    if ($delivery->validate()) {
                        $delivery->save();
                    }
                }

                return $this->renderAjax('_courierCall', [
                    'ordersCourierCall' => $ordersCourierCall,
                    'searchModel'       => $searchModel,
                    'orders'            => $orders
                ]);

            } else {
                if ($ordersCourierCall->orderIds == false || $ordersCourierCall->carriers == false) {
                    throw new UserException(400, Yii::t('app', 'For selected orders, a courier can not be called'));
                }

                return $this->renderAjax('_courierCall', [
                    'ordersCourierCall' => $ordersCourierCall,
                    'searchModel'       => $searchModel,
                ]);
            }
        } catch (\Exception $e) {
            Yii::$app->session->addFlash(
                'danger',
                Yii::t('app', $e->getMessage() . '<br /> ' . 'Если это заказы собственной службы доставки, переведите заказ в статус в карточке заказа')
            );
            return $this->renderAjax('_courierCall', [
                'ordersCourierCall' => $ordersCourierCall,
                'searchModel'       => $searchModel,
            ]);
        }
    }

    /**
     * @return string
     */
    public function actionReadyForDelivery()
    {
        $searchModel  = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams + ['status' => 'redyForDelivery']);

        return $this->render('readyForDelivery', [
            'searchModel'                => $searchModel,
            'dataProvider'               => $dataProvider,
            'isShowCreateRegistryButton' => Yii::$app->user->can('/courier/create'),
            'hasDeliveryErrorOrders'     => $searchModel->hasDeliveryErrorOrders(),
        ]);
    }

    /**
     * @return string
     * @throws UserException
     */
    public function actionGetCourierOrders()
    {
        if ($courierId = Yii::$app->request->post('courierId')) {
            $courier      = Courier::find()->where('id=' . $courierId)->one();
            $searchModel  = new OrderSearch();
            $dataProvider = new ActiveDataProvider([
                'query' => Order::find()->joinWith(['delivery', 'shop', 'courier'])->andWhere('courier.id = ' . $courier->id),
                'sort'  => false
            ]);

            return $this->renderAjax('_courierOrders', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
                'courier'      => $courier
            ]);
        } else {
            throw new UserException(400, Yii::t('app', 'Missing Courier ID'));
        }
    }

    /**
     * @return array
     * @throws UserException
     */
    public function actionSetCancelStatusToOrders()
    {
        if ($orderIds = Yii::$app->request->post('selection')) {
            $orders = (new Order())->getCancelProperOrders($orderIds);

            if (count($orderIds) != count($orders)) {
                throw new UserException(412, Yii::t('app', 'Wrong order status'));
            }

            /** @var Order $order */
            foreach ($orders as $order) {
                try {
                    if ($order->sendToStatus(Order::STATUS_CANCELED)) {
                        (new Query())->createCommand()->update('{{%order}}', ['status' => $order->status], ['id' => $order->id])->execute();
                    }
                } catch (Exception $e) {
                    throw new UserException(422, Yii::t('app', 'It is not possible transfer order #{order} to cancel status', [$order->id]));
                }
            }

            Yii::$app->session->addFlash('success', Yii::t('app', 'Orders was canceled'));
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'url' => Url::to(['order/index'])
            ];
        } else {
            throw new UserException(404, Yii::t('app', 'Order id is not selected'));
        }
    }

    /**
     * Расчет стоимости доставки
     *
     * @return string
     */
    public function actionCalculateCarriers()
    {
        $post  = Yii::$app->request->post();
        $order = (new \app\models\builder\Order($post))
            ->setScenario(Order::SCENARIO_CALCULATE)
            ->build();

        if (!$order->validate()) {
            return $this->renderAjax('_deliveries_errors', [
                'errors' => $order->firstErrors,
            ]);
        }

        $calculator = $order->getCalculator();

        if ($calculator->prepareData() && $calculator->validate()) {
            $orderDelivery = new OrderDelivery();
            /** @var Delivery $deliveries */
            $deliveries      = Yii::createObject(\app\delivery\Delivery::className(), [
                $calculator,
                $orderDelivery
            ]);
            $orderDeliveries = Yii::$app->cache->getOrSet(
                $calculator->getCacheParameters(),
                function () use ($deliveries) {
                    $calculatedDeliveries = $deliveries->calculate();
                    return empty($calculatedDeliveries) ? [] : $calculatedDeliveries;
                },
                3600
            );

            $filter          = ArrayHelper::getValue($post, 'filter', 'cheapest');
            $orderDeliveries = $orderDelivery->getFilteredDeliveries($orderDeliveries, $filter);

            return $this->renderAjax('_deliveries', [
                'orderDeliveries' => $orderDeliveries,
                'disabledEdit'    => (bool)$order->disabledEdit,
                'isPartial'       => (bool)$order->partial,
                'filter'          => $filter,
            ]);
        } else {
            return $this->renderAjax('_deliveries_errors', [
                'errors' => $calculator->errors,
            ]);
        }
    }

    /**
     * @param $i
     * @return string
     */
    public function actionGetProductRow($i)
    {
        $orderProduct = new OrderProduct(['product' => new Product()]);

        return $this->renderAjax('_product', [
            'orderProduct' => $orderProduct,
            'disabledEdit' => false,
            'i'            => ++$i,
        ]);
    }

    /**
     * Применить доставку к заказу
     *
     * @return null|string
     */
    public function actionApplyDelivery()
    {

        $post          = Yii::$app->request->post();
        $orderDelivery = new OrderDelivery();

        $order          = new Order();
        $order->address = new Address();
        $products       = [];

        $order->load($post);
        $order->address->load($post);

        foreach (Yii::$app->request->post('Product', []) as $i => $productRequest) {
            $products[$i]          = new Product();
            $products[$i]->shop_id = $order->shop_id;
        }
        Product::loadMultiple($products, $post);
        foreach ($products as $product) {
            if ($product->weight !== '') {
                $product->weight = str_replace(',', '.', $product->weight) * 1000;
            }
        }

        $order->products = $products;

        if ($orderDelivery->load($post, '') && $orderDelivery->loadDelivery($order, $post['OrderDelivery'])) {
            Yii::$app->response->format = Response::FORMAT_HTML;
            return $this->renderAjax('_delivery', [
                'orderDelivery' => $orderDelivery,
                'orderDisabled' => $order->disabledEdit,
            ]);
        }

        return null;
    }

    /**
     * @param $id
     * @param $status
     * @return Response|string
     */
    public function actionSetStatus($id, $status)
    {
        $this->setStatus($id, $status);
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param int $id
     * @param string $status
     * @return bool
     */
    private function setStatus(int $id, string $status): bool
    {
        /** @var Order $order */
        $order     = $this->findModel($id);
        $oldStatus = $order->status;

        try {
            if ($order->sendToStatus($status)) {
                (new Query())->createCommand()->update('{{%order}}', ['status' => $order->status], ['id' => $order->id])->execute();

                // TODO перенести логирование в одно место так как сейчас слишком рарозненно
                LogBehavior::setSingleLog(
                    'status',
                    $oldStatus,
                    (new Order())->getWorkflowStatusId($status),
                    'Order',
                    $order->id
                );

                if (Order::isStatusEquals($order->status, Order::STATUS_DELIVERY_ERROR)) {
                    $lavel = 'warning';
                } else {
                    $lavel = 'success';
                }

                // Добавим в сессию ID заказа для того чтобы показать модальное окно
                Yii::$app->session->set('id', $order->id);
                Yii::$app->session->addFlash($lavel, Yii::t('app', 'Order is transferred to status "{statusName}"', [
                    'statusName' => WorkflowHelper::getLabel($order),
                ]));
                return true;
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Order can not be transferred to a status'));
                return false;
            }
        } catch (\Exception $e) {
            if ($e->getCode() == ErrorException::ERROR_NOT_ENOUGH_PRODUCTS) {
                if ($order->sendToStatus(Order::STATUS_PRESALE)) {
                    (new Query())->createCommand()->update('{{%order}}', ['status' => $order->status], ['id' => $order->id])->execute();
                    Yii::$app->session->addFlash('warning', $e->getMessage());
                    Yii::$app->session->addFlash('warning', Yii::t('app', 'Order is transferred to status "{statusName}"', [
                        'statusName' => WorkflowHelper::getLabel($order),
                    ]));
                    Yii::$app->session->addFlash('warning', Yii::t('app', 'You can collect the order, when the necessary quantity of goods will be available in the warehouse'));
                }
            } else {

//                Yii::$app->session->addFlash('danger', $e->getTraceAsString());

                Yii::$app->session->addFlash('danger', $e->getMessage());
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Order can not be transferred to a status'));
            }
            Yii::error("Ошибка перевода в статус: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     *
     */
    protected function closeModal()
    {
        echo '<script>
                    var id = $(\'.grid-view\').parents(\'[data-pjax-container]\').attr(\'id\');
                    $(\'.modal:visible\').modal(\'hide\');
                    if (id !== undefined) {
                        $.pjax.reload({container:\'#\'+id});
                    }
              </script>';
    }

    /**
     * Сохраняет сообщение для заказа
     *
     * @return array
     */
    public function actionSaveMessage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $message                    = new OrderMessage();

        $message->message  = Yii::$app->request->post('message');
        $message->order_id = Yii::$app->request->post('order_id');

        $success = false;
        if ($message->validate()) {
            $message->save();
            $success = true;

            // TODO перенести логирование в одно место так как сейчас слишком рарозненно
            LogBehavior::setSingleLog(
                'message',
                null,
                $message->message,
                'OrderMessage',
                $message->order_id,
                $message->id
            );
        }

        return ['success' => $success, 'errorMessage' => $message->errors];
    }

    /**
     * Клонирование заказа
     * Заказ создается в статусе Создан, с ID по версии Fastery
     *
     * @return \yii\base\Response
     */
    public function actionCopy()
    {
        try {
            if (!$id = Yii::$app->request->get('id')) {
                throw new Exception(Yii::t('app', 'Order id is not specified'));
            }

            if (!$order = Order::find()->where(['id' => $id])->one()) {
                throw new Exception(Yii::t('app', 'Order was not founded'));
            }

            if ($newOrder = $order->getCopiedOrder($order)) {
                // Копируем продукты
                foreach ($order->orderProducts as $product) {
                    $newOrder->cloneOrderProduct($product);
                }
//                $newOrder->cloneOrderDelivery(OrderDelivery::find()->where(['order_id' => $id])->one());
                Yii::$app->session->addFlash('success', Yii::t('app', 'Order was cloned'));
                return $this->redirect(['view', 'id' => $newOrder->id]);

            } else {
                throw new Exception(implode('\n', $newOrder->getFirstErrors()));
            }

        } catch (\Exception $e) {
            Yii::$app->session->addFlash('danger', $e->getMessage());
            Yii::error(print_r($e, true), __METHOD__);
            return $this->redirect(['index']);
        }
    }

    /**
     * Получает историю статусов заказа
     *
     * @return string
     */
    public function actionStatusHistory()
    {
        if ($orderId = Yii::$app->request->get('id')) {
            /** @var Order $order */
            $order = $this->findModel($orderId);
            return $this->renderAjax('_history', [
                'order'    => $order,
                'statuses' => DeliveryStatus::find()->andWhere(['order_id' => $orderId])->orderBy(['status_date' => SORT_ASC])->all()
            ]);
        }
    }

    /**
     * Модальное окно для статусов Подтвержден и Готов к отгрузке
     *
     * @return string
     */
    public function actionInCollectingWindow()
    {
        $order = Order::find()->where(['id' => Yii::$app->request->get('orderId')])->one();

        if ($order->shop->fulfillment) {
            $title   = Yii::t('app', 'Send order to collecting');
            $message = Yii::t('app', 'Collecting text');
            $button  = Yii::t('app', 'On collecting');
        } else {
            $title   = Yii::t('app', 'Confirm order');
            $message = Yii::t('app', 'Confirm text');
            $button  = Yii::t('app', 'Confirm');
        }

        $statusKey = $order->getWorkflowStatusKey($order->status);

        // TODO сделать нормальный метод получения текстов
        if ($statusKey == Order::STATUS_CONFIRMED) {
            $title   = Yii::t('app', 'Order is transferred to status "{statusName}"', [
                'statusName' => WorkflowHelper::getLabel($order),
            ]);
            $message = Yii::t('app', 'Call courier text');
            $button  = Yii::t('app', 'Call the courier');
        }

        if ($status = Yii::$app->request->get('status')) {
            try {
                if ($order->sendToStatus($status)) {
                    (new Query())->createCommand()->update('{{%order}}', ['status' => $order->status], ['id' => $order->id])->execute();

                    if (ArrayHelper::getValue(Yii::$app->params, 'demo')) {
                        (new Query())->createCommand()->update(
                            '{{%order}}',
                            ['provider_number' => $order->id],
                            ['id' => $order->id]
                        )->execute();
                    }

                    if (Order::isStatusEquals($order->status, Order::STATUS_DELIVERY_ERROR)) {
                        $lavel = 'warning';
                    } else {
                        $lavel = 'success';
                    }

                    $statusKey = Order::STATUS_IN_COLLECTING;
                    if ($order->shop->fulfillment) {
                        $title   = Yii::t('app', 'Order is transferred to status "{statusName}"', [
                            'statusName' => WorkflowHelper::getLabel($order),
                        ]);
                        $message = '';
                        $button  = '';
                    } else {
                        $title   = Yii::t('app', 'Order is transferred to status "{statusName}"', [
                            'statusName' => WorkflowHelper::getLabel($order),
                        ]);
                        $message = Yii::t('app', 'Call courier text');
                        $button  = Yii::t('app', 'Call the courier');
                    }

                    Yii::$app->session->removeFlash($lavel);
                    Yii::$app->session->addFlash($lavel, $title);

                } else {
                    Yii::$app->session->addFlash('danger', Yii::t('app', 'Order can not be transferred to a status'));
                    $title = Yii::t('app', 'Order can not be transferred to a status');
                }
            } catch (\Exception $e) {
                Yii::$app->session->addFlash('danger', $e->getMessage());
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Order can not be transferred to a status'));
                $title = Yii::t('app', 'Order can not be transferred to a status');
                Yii::error(print_r($e, true), __METHOD__);
            }
        }

        return $this->renderAjax('_inCollecting', [
            'order'   => $order,
            'title'   => $title,
            'message' => $message,
            'button'  => $button,
            'status'  => $statusKey
        ]);
    }

    /**
     * Получаем форматированные итоги заказа по входным данным.
     */
    public function actionCalculateTotals()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $sum           = (float)Yii::$app->request->get('sum');
        $accessedSum   = (float)Yii::$app->request->get('accessed_sum');
        $deliverySum   = (float)Yii::$app->request->get('delivery_sum');
        $paymentMethod = Yii::$app->request->get('payment_method');

        return [
            [
                'label' => Yii::t('app', 'Products Cost:'),
                'value' => Yii::$app->formatter->asCurrency($sum, 'RUB')
            ],
            [
                'label' => Yii::t('app', 'Accessed Cost:'),
                'value' => Yii::$app->formatter->asCurrency($accessedSum, 'RUB')
            ],
            [
                'label' => Yii::t('app', 'Delivery Cost:'),
                'value' => Yii::$app->formatter->asCurrency($deliverySum, 'RUB')
            ],
            [
                'label' => Yii::t('app', 'Cod Cost:'),
                'value' => Yii::$app->formatter->asCurrency(
                    (new Order())->getCodCost($sum, $deliverySum, $paymentMethod),
                    'RUB'
                )
            ]
        ];
    }

    /**
     * Контроллер получения списка доступных методов оплаты
     *
     * @param $carrierKey
     * @param $serviceKey
     * @return array
     */
    public function actionPaymentMethodsByCarrierKey($carrierKey, $serviceKey)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $carrierPayments            = Order::getPaymentMethodsByCarrierKey($carrierKey);
        $servicePayments            = Order::getPaymentMethodsByServiceKey($serviceKey);
        return array_intersect_key($carrierPayments, $servicePayments); //Order::getPaymentMethodsByCarrierKey($carrierKey);
    }

    /**
     * Получение списка активных колонок для таблицы заказов
     */
    public function actionColumns()
    {
        $orderSearch              = new OrderSearch();
        $orderSearch->userColumns = $orderSearch->getUserColumns();

        if ($orderSearch->load(Yii::$app->request->post()) && count($orderSearch->userColumns)) {
            Yii::$app->session->addFlash('success', 'Вы успешно изменили отображение колонок с данными');
            $orderSearch->setUserColumns($orderSearch->userColumns);
            return $this->redirect(Url::to(['index']));
        }

        return $this->renderAjax('_columns', [
            'orderSearch' => $orderSearch,
            'columns'     => OrderSearch::getOrderColumns()
        ]);
    }

    /**
     * Получение списка ID доступных заказов
     *
     * @return array
     */
    public function actionGetAvailableOrders()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data                       = Yii::$app->request->post();

        $model = Order::findOne($data['selection'][0]);

        if (!$model->delivery) {
            return [];
        }

        $orders = Order::find()
            ->joinWith(['delivery'])
            ->where(['order.id' => explode(',', $data['orderIds'])])
            ->andWhere(['order_delivery.carrier_key' => $model->delivery->carrier_key])
            ->andWhere(['order.provider_number' => null])
            ->andWhere(['order.status' => $model->getWorkflowStatusId(Order::STATUS_CREATED)])
            ->all();

        $availableOrderIds = [];
        foreach ($orders as $order) {
            if ($order->delivery
                && $order->products
                && count($order->products)
                && $order->address
            ) {
                $availableOrderIds[] = $order->id;
            }
        }

        return [
            'ids' => $availableOrderIds
        ];
    }

    /**
     * Получение списка ID доступных заказов
     *
     * @return array
     */
    public function actionIsConfirmAllowed(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $orders = Order::find()->where(['id' => Yii::$app->request->post('selection')])->all();

        $errorOrderIds       = [];
        $carriers            = [];
        $errorStatusOrderIds = [];
        $messages            = [];

        foreach ($orders as $order) {
            if (!$order->delivery
                || !$order->products
                || !count($order->products)
                || !$order->address
            ) {
                $errorOrderIds[] = $order->id;
            }

            if ($order->delivery) {
                $carriers[$order->delivery->carrier_key][] = $order->id;
            }

            if ($order->getWorkflowStatusKey($order->status) != Order::STATUS_CREATED) {
                $errorStatusOrderIds[] = $order->id;
            }

        }

        if (count($errorOrderIds)) {
            $messages[] = sprintf("Проверьте выбранные заказы.\n Заказ(ы) %s не заполнены и не могут перевестить в статус Подтвержден", implode(', ', $errorOrderIds));
        }

        if (!count($orders)) {
            $messages[] = sprintf("Вы не выбрали ни один заказ для подтверждения.\n Выберите хотя бы 1 заказ.");
        }

        if (count($carriers) > 1) {
            $messages[] = sprintf("Вы выбрали заказы с разных СД.\n Вы можете отправить на подтверждение группой только заказы одной СД");
        }

        if (count($errorStatusOrderIds) > 0) {
            $messages[] = sprintf("Вы выбрали заказ(ы) %s в статусах отличных от статуса Новый.\n Для подтверждения выберите заказы в статусе Новый", implode(', ', $errorStatusOrderIds));
        }

        if (count($messages)) {
            $data = [
                'error' => [
                    'messages' => $messages
                ]
            ];
        } else {
            $data = [
                'title'   => 'Подтвердить выбранные заказы?',
                'message' => 'Отмеченные заказы будут подтверждены а так же переданы в службу доставки'
            ];
        }

        return $data;
    }

    /**
     * Множественное подтверждение заказов
     *
     * @return array
     */
    public function actionMultiConfirm(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $orderIds                   = Yii::$app->request->post('selection');
        $value                      = true;
        foreach ($orderIds as $id) {
            $order         = Order::findOne($id);
            $isFulfillment = $order->shop->fulfillment;
            $value         = $this->setStatus($id, $isFulfillment ? Order::STATUS_IN_COLLECTING : Order::STATUS_CONFIRMED);
            if (!$value) {
                $value = false;
            }
        }
        return [
            'status' => $value
        ];
    }

    /**
     * Получение звонков по заказу
     *
     * @param int $orderId
     * @return string
     */
    public function actionCalls(int $orderId): string
    {
        return $this->renderAjax('_calls', [
            'calls' => Call::find()->where(['order_id' => $orderId])->orderBy(['call_id' => SORT_DESC])->all()
        ]);
    }

    /**
     * Получение текущего статуса заказа
     *
     * @param int $orderId
     * @return string
     */
    public function actionGetCurrentStatus(int $orderId): string
    {
        $order = Order::find()->where(['id' => $orderId])->one();

        /** @var Deliveries $deliveries */
        $deliveries = Yii::createObject(Deliveries::className(), [$order, null]);
        $data       = $deliveries->getCurrentStatus();

        if (!isset($data['orderInfo'])) {
            return $this->renderAjax('/common/_notFound');
        }

        return $this->renderAjax('_currentStatus', [
            'title'    => Yii::t('app', 'Actual status'),
            'order'    => [
                'key'    => $order->delivery->carrier_key,
                'name'   => $order->delivery->getDeliveryName(),
                'number' => isset($data['orderInfo']) ? $data['orderInfo']['providerNumber'] : null,
            ],
            'status'   => [
                'name'        => $data['status']['providerName'] ? $data['status']['providerName'] : '---',
                'description' => $data['status']['providerDescription'] ? $data['status']['providerDescription'] : '---',
                'date'        => $data['status']['createdProvider'] ? date('d.m.Y в H:i', strtotime($data['status']['createdProvider'])) : '---'
            ],
            'provider' => [
                'name'        => $data['status']['name'],
                'description' => $data['status']['description'] ? $data['status']['description'] : '---',
                'date'        => $data['status']['created'] ? date('d.m.Y в H:i', strtotime($data['status']['created'])) : '---'
            ],
        ]);
    }

    /**
     * Валидация адреса
     *
     * @param string $addressFull
     * @param int $shopId
     * @param string $region
     * @param string $city
     * @param string $street
     * @param string $house
     * @param string $housing
     * @param string $flat
     * @param string $postcode
     * @return array
     */
    public function actionCheckAddress(
        string $addressFull,
        int $shopId,
        string $region,
        string $city,
        string $street,
        string $house,
        string $housing,
        string $flat,
        string $postcode
    ): array {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $shop                       = Shop::findOne($shopId);
        $errors                     = [];

        if ($shop->parse_address) {

            try {
                $address = (new Address())->getPerformed($addressFull);

                if (!$address) {
                    return $errors;
                }

                if ($address->region != $region) {
                    $errors['region'] = Yii::t(
                        'order',
                        'Region is mismatch <b>{userData}</b>: <b>{systemData}</b>',
                        ['userData' => $region, 'systemData' => $address->region]
                    );
                }
                if ($address->city != $city) {
                    $errors['city'] = Yii::t(
                        'order',
                        'City is mismatch <b>{userData}</b>: <b>{systemData}</b>',
                        ['userData' => $city, 'systemData' => $address->city]
                    );
                }
                if ($address->street != $street) {
                    $errors['street'] = Yii::t(
                        'order',
                        'Street is mismatch <b>{userData}</b>: <b>{systemData}</b>',
                        ['userData' => $street, 'systemData' => $address->street]
                    );
                }
                if ($address->postcode != $postcode) {
                    $errors['postcode'] = Yii::t(
                        'order',
                        'Postcode is mismatch <b>{userData}</b>: <b>{systemData}</b>',
                        ['userData' => $postcode, 'systemData' => $address->postcode]
                    );
                }
                if ($address->housing != $housing) {
                    $errors['housing'] = Yii::t(
                        'order',
                        'Housing is mismatch <b>{userData}</b>: <b>{systemData}</b>',
                        ['userData' => $housing, 'systemData' => $address->housing]
                    );
                }
                if ($address->house != $house) {
                    $errors['house'] = Yii::t(
                        'order',
                        'House is mismatch <b>{userData}</b>: <b>{systemData}</b>',
                        ['userData' => $house, 'systemData' => $address->house]
                    );
                }
                if ($address->flat != $flat) {
                    $errors['flat'] = Yii::t(
                        'order',
                        'Flat is mismatch <b>{userData}</b>: <b>{systemData}</b>',
                        ['userData' => $flat, 'systemData' => $address->flat]
                    );
                }
                if (!empty($errors)) {
                    $errors['total'] = Yii::t(
                        'order',
                        'Verify that the address you entered is correct. If the address is correct, you can click the "Confirm" button and continue editing the order.'
                    );
                }
            } catch (\Exception $e) {
                return $errors;
            }
        }
        return $errors;
    }

    /**
     * Получение uri для звонка
     *
     * @param int $shopId
     * @param int $orderId
     * @param string $clientPhone
     * @return array
     */
    public function actionGetCallUrl(
        int $shopId,
        int $orderId,
        string $clientPhone
    ): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $shop  = Shop::findOne($shopId);
        $order = $orderId ? Order::findOne($orderId) : (new Order());

        if ($clientPhone) {
            $order->phone = $clientPhone;
        }

        return $shop->getShopCallUrl($order);
    }

    /**
     * @param string $pickupDate
     * @param string $deliveryDate
     * @param int $minTerm
     * @return array
     */
    public function actionGetDeliveryDate(
        string $pickupDate,
        string $deliveryDate,
        int $minTerm
    ): array {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'deliveryDate' => (new Helper\Date(strtotime($pickupDate)))
                ->setFormat('d.m.Y')
                ->getNearestDeliveryDate($minTerm, strtotime($deliveryDate))
        ];
    }

    /**
     * @param int $id
     * @return string
     */
    public function actionSetDimensions(int $id): string
    {
        $order         = Order::findOne($id);
        $order->weight = $order->getWeight() / 1000;

        return $this->renderAjax('_setDimensions', [
            'order' => $order,
            'title' => Yii::t('order', 'Would you like set dimensions'),
        ]);
    }

    /**
     * Расчет стоимости доставки при указании габаритов
     *
     * @param int $id
     * @return string
     */
    public function actionCalculate(int $id)
    {
        $post  = Yii::$app->request->post();
        $order = Order::findOne($id)->prepareData($post);

        if (!$order) {
            return $this->renderAjax('_deliveries_errors', [
                'errors' => $order->firstErrors,
            ]);
        }

        $calculator = $order->getCalculator();
        if ($calculator->prepareData() && $calculator->validate()) {
            $orderDelivery = new OrderDelivery();
            /** @var Deliveries $deliveries */
            $deliveries = Yii::createObject(\app\delivery\Delivery::className(), [
                $calculator,
                $orderDelivery
            ]);

            $orderDeliveries = Yii::$app->cache->getOrSet(
                $calculator->getCacheParameters(),
                function () use ($deliveries) {
                    $orderDeliveries = $deliveries->calculate();
                    return empty($orderDeliveries) ? [] : $orderDeliveries;
                },
                3600
            );

            $filter = ArrayHelper::getValue($post, 'filter', 'cheapest');

            // Получим выбранную ранее доставку
            $chosenDelivery = $orderDelivery->getChosenDelivery(
                $orderDeliveries,
                $order->delivery->carrier_key,
                $order->delivery->type
            );

            // Получим самую дешевую доставку для выбранного ранее типа
            $cheapestDelivery = $orderDelivery->getCheapestDelivery(
                $orderDeliveries,
                $chosenDelivery->cost ?? 0,
                $order->delivery->type
            );

            $orderDeliveries = $orderDelivery->getFilteredDeliveries($orderDeliveries, $filter);

            return $this->renderAjax('_dimensionDeliveries', [
                'order'            => $order,
                'orderDeliveries'  => $orderDeliveries,
                'chosenDelivery'   => $chosenDelivery,
                'cheapestDelivery' => $cheapestDelivery,
                'disabledEdit'     => (bool)$order->disabledEdit,
                'isPartial'        => (bool)$order->partial,
                'filter'           => $filter,
            ]);
        } else {
            return $this->renderAjax('_deliveries_errors', [
                'errors' => $calculator->errors,
            ]);
        }
    }

    /**
     * Приминение доставки к заказу
     *
     * @param int $id
     * @return null|string
     */
    public function actionApplyDimensionDelivery(int $id)
    {
        $post          = Yii::$app->request->post();
        $order         = Order::findOne($id)->prepareData($post);
        $orderDelivery = new OrderDelivery();

        if ($orderDelivery->load($post, '') && $orderDelivery->loadDelivery($order, $post['OrderDelivery'])) {
            Yii::$app->response->format = Response::FORMAT_HTML;
            return $this->renderAjax('_dimensionDelivery', [
                'orderDelivery' => $orderDelivery,
                'orderDisabled' => $order->disabledEdit,
            ]);
        }

        return null;
    }

    /**
     * Обновление габаритов
     *
     * @param int $id
     * @return null|array
     */
    public function actionUpdateDimensions(int $id)
    {
        $post  = Yii::$app->request->post();
        $order = Order::findOne($id)->prepareData($post);

        $order->delivery->load($post);
        $order->partial = $order->delivery->partial;

        $validateDelivery = $order->delivery->loadDelivery(
                $order,
                Yii::$app->request->post('OrderDelivery')
            ) && $order->delivery->validate();

        if ($validateDelivery
            && $order->save()
        ) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => 'true'];
        }
    }

    /**
     * Проверка доступности возможности архивации заказов
     *
     * @return array
     */
    public function actionIsArchiveAllowed(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $orderIds                   = Yii::$app->request->post('selection');
        $orders                     = (new Orders())->setOrders($orderIds ?? []);
        return [
            'title'   => Yii::t('order', 'Archive orders?'),
            'message' => Yii::t('order', 'Do you realy want archive orders?'),
            'status'  => $orders->checkArchiveAvailability(),
            'errors'  => $orders->getErrorMessages()
        ];
    }

    /**
     * Архивирование заказов
     *
     * @return array
     */
    public function actionMultiArchive(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $orderIds                   = Yii::$app->request->post('selection');
        return [
            'status' => (new Orders())->setOrders($orderIds)->archive()
        ];
    }

    /**
     * Разархивирование заказов
     *
     * @return array
     */
    public function actionMultiUnArchive(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $orderIds                   = Yii::$app->request->post('selection');
        return [
            'status' => (new Orders())->setOrders($orderIds)->unArchive()
        ];
    }

    /**
     * Обновление заказа в СД
     *
     * @param int $orderId
     * @return array
     */
    public function actionReSend(int $orderId): array
    {
        $order = Order::findOne($orderId);
        return [
            'success' => $order->reSendOrder()
        ];
    }
}
