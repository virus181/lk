<?php
namespace app\api\controllers;

use app\api;
use app\api\models\Tracker;
use app\api\Module;
use app\components\Clients\Dadata;
use app\components\UserException;
use app\models\Address;
use app\models\DeliveryStatus;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Product;
use app\models\User;
use raoul2000\workflow\base\WorkflowException;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OrderController extends api\base\BaseActiveController
{
    public $modelClass = 'app\models\Order';
    public $searchModelClass = 'app\models\search\OrderSearch';

    /**
     * @param string $id
     * @param Module $module
     * @param array  $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        Yii::$app->params['environment'] = 'api';
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'index' => [
                'class'       => 'yii\rest\IndexAction',
                'modelClass'  => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ]
        ];
    }

    /**
     * Создание заказа
     *
     * @return Order
     */
    public function actionCreate()
    {
        $request  = Yii::$app->request;
        $event    = new \app\events\Order();
        $order    = new Order;
        $address  = new Address();
        $delivery = new OrderDelivery();
        $products = [];

        $order->scenario = Order::SCENARIO_CREATE_FROM_API;
        $order->load($request->post(), '');
        $order->is_api = true;

        if ($request->post('products')) {
            foreach ($request->post('products', []) as $i => $productRequest) {
                $products[$i]           = new Product();
                $products[$i]->scenario = Product::SCENARIO_CREATE_ORDER;
                $products[$i]->shop_id  = $order->shop_id;
            }
            Product::loadMultiple($products, $request->post(), 'products');
            $validateProducts = Product::validateMultiple($products);
            $order->products  = $products;
        } else {
            $validateProducts = true;
        }

        if ($request->post('delivery')) {
            $delivery->load($request->post(), 'delivery');
            $order->partial = $delivery->partial;

            if ($delivery->type === OrderDelivery::DELIVERY_TO_DOOR) {
                $address->scenario = Address::SCENARIO_ADDRESS_API_FULL;
            } elseif ($request->post('delivery')) {
                $address->scenario = Address::SCENARIO_ADDRESS_API_TO_CITY;
            }

            $address->load($request->post(), 'address');

            if ($order->shop->parse_address && empty($address->city_fias_id) && $performedAddress = $address->getPerformed()) {
                $address = $performedAddress;
            } elseif (!$address->city_fias_id) {
                $city                  = (new Dadata())->getCity($address->city);
                $address->city_fias_id = !empty($city) ? $city['data']['fias_id'] : null;
            }

            $order->address = $address;

            $validateAddress = $address->validate();

            if ($delivery->loadDelivery($order, $request->post('delivery'))) {
                $validateDelivery = $delivery->validate();
            } else {
                $validateDelivery = false;
            }

            if (!$validateAddress) {
                $delivery->clearErrors();
            }

            $order->delivery = $delivery;
        } elseif ($request->post('address')) {
            $validateDelivery = true;
            $address->load($request->post(), 'address');

            if ($order->shop->parse_address && $performedAddress = $address->getPerformed()) {
                $address = $performedAddress;
            } elseif (!$address->city_fias_id) {
                $city                  = (new Dadata())->getCity($address->city);
                $address->city_fias_id = !empty($city) ? $city['data']['fias_id'] : null;
            }

            $validateAddress = $address->validate();
            $order->address  = $address;
        } else {
            $validateAddress  = true;
            $validateDelivery = true;
        }

        if (Yii::$app->request->get('expand') === null) {
            Yii::$app->request->setQueryParams(['expand' => 'products,delivery,address']);
        }

        $order->sendToStatus(Order::STATUS_CREATED);
        $validateOrder = $order->validate();

        if (
            $validateProducts &&
            $validateAddress &&
            $validateDelivery &&
            $validateOrder &&
            $order->save(false)
        ) {
            $event->setOrder($order)->setIsApi(true)->prepareEvent();
            Yii::$app->trigger(\app\events\Order::EVENT_ORDER_CREATED, $event);

            return $order;
        } else {
            $modelsErrors = [];

            if ($validateAddress == false) {
                $modelsErrors['address'] = $address->errors;
            }
            if ($validateDelivery == false) {
                $modelsErrors['delivery'] = $delivery->errors;
            }
            if ($validateProducts == false) {
                /** @var Product $product */
                foreach ($products as $i => $product) {
                    $modelsErrors['products.' . $i] = $product->errors;
                }
            }

            foreach ($modelsErrors as $modelName => $errors) {
                foreach ($errors as $attribute => $message) {
                    $order->addError("$modelName.$attribute", implode('; ', $message));
                }
            }

            return $order;
        }
    }

    /**
     * Обновление заказа
     *
     * @param $id
     * @return null|static
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $order = Order::findOne($id);

        if ($order === null) {
            throw new NotFoundHttpException(Yii::t('app', 'Заказ с id {id} не найден', ['id' => $id]));
        }

        if (!in_array($order->status, Order::STATUS_EDITABLE)) {
            throw new BadRequestHttpException(Yii::t('app', 'Заказ не может быть отредактирован'));
        }

        $request  = Yii::$app->request;
        $products = [];

        $order->scenario = Order::SCENARIO_CREATE_FROM_API;
        $order->load($request->post(), '');
        $order->is_api = true;

        if ($request->post('products')) {
            foreach ($request->post('products', []) as $i => $productRequest) {
                $products[$i]          = new Product();
                $products[$i]->shop_id = $order->shop_id;
            }
            Product::loadMultiple($products, $request->post(), 'products');
            $order->products  = $products;
            $validateProducts = Product::validateMultiple($products);
        } else {
            $validateProducts = true;
        }

        if ($request->post('delivery')) {
            if ($order->delivery === null) {
                $order->delivery = new OrderDelivery();
            }
            if ($order->address === null) {
                $order->address = new Address();
            }

            $order->delivery->load($request->post(), 'delivery');

            if ($order->delivery->type === OrderDelivery::DELIVERY_TO_DOOR) {
                $order->address->scenario = Address::SCENARIO_ADDRESS_API_FULL;
            } elseif ($request->post('delivery')) {
                $order->address->scenario = Address::SCENARIO_ADDRESS_API_TO_CITY;
            }

            if ($order->address->load($request->post(), 'address') && $order->shop->parse_address) {
                $address           = new Address();
                $address->scenario = $order->address->scenario;
                $address->load($request->post(), 'address');
                $performedAddress = $address->getPerformed();
                $order->address   = $performedAddress;
            } elseif (!$order->address->city_fias_id) {
                $city                         = (new Dadata())->getCity($order->address->city);
                $order->address->city_fias_id = !empty($city) ? $city['data']['fias_id'] : null;
            }

            $validateAddress = $order->address->validate();

            if ($order->delivery->loadDelivery($order, $request->post('delivery'))) {
                $validateDelivery = $order->delivery->validate();
            } else {
                $validateDelivery = false;
            }

            if (!$validateAddress) {
                $order->delivery->clearErrors();
            }
        } elseif ($request->post('address')) {
            if ($order->address === null) {
                $order->address = new Address();
            }
            $validateDelivery = true;
            $order->address->load($request->post(), 'address');
            if ($order->shop->parse_address && $performedAddress = $order->address->getPerformed()) {
                $order->address = $performedAddress;
            } elseif (!$order->address->city_fias_id) {
                $city                         = (new Dadata())->getCity($order->address->city);
                $order->address->city_fias_id = !empty($city) ? $city['data']['fias_id'] : null;
            }
            $validateAddress = $order->address->validate();
        } else {
            $validateAddress  = true;
            $validateDelivery = true;
        }

        if (Yii::$app->request->get('expand') === null) {
            Yii::$app->request->setQueryParams(['expand' => 'products,delivery,address']);
        }

        $order->sendToStatus(Order::STATUS_CREATED);
        $validateOrder = $order->validate();

        if (
            $validateProducts &&
            $validateAddress &&
            $validateDelivery &&
            $validateOrder &&
            $order->save(false)
        ) {
            return $order;
        } else {
            $modelsErrors = [];

            if ($validateAddress == false) {
                $modelsErrors['address'] = $order->address->errors;
            }
            if ($validateDelivery == false) {
                $modelsErrors['delivery'] = $order->delivery->errors;
            }
            if ($validateProducts == false) {
                /** @var Product $product */
                foreach ($order->products as $i => $product) {
                    $modelsErrors['products.' . $i] = $product->errors;
                }
            }

            foreach ($modelsErrors as $modelName => $errors) {
                foreach ($errors as $attribute => $message) {
                    $order->addError("$modelName.$attribute", implode('; ', $message));
                }
            }
            return $order;
        }
    }

    /**
     * Множественное получение статусов заказов
     *
     * @return ArrayDataProvider
     */
    public function actionStatuses()
    {
        $orders = Order::find()
            ->select(['id', 'status', 'created_at', 'updated_at', 'delivery_status'])
            ->andWhere(['IN', 'order.id', explode(',', Yii::$app->request->get('id'))])
            ->all();

        $result = [];
        foreach ($orders as $key => $order) {
            $result[$key]['id']     = $order->id;
            $result[$key]['status'] = $order->getWorkflowStatusName($order->status);
        }

        $arrayDataProvider = new ArrayDataProvider([
            'allModels'  => $result,
            'pagination' => [
                'class'         => Pagination::className(),
                'pageSizeLimit' => [1, count($orders)],
                'pageSize'      => count($orders),
            ],
        ]);

        return $arrayDataProvider;
    }

    /**
     * Получение истории статусов заказа по id или shop_order_number
     *
     * @return ArrayDataProvider
     */
    public function actionStatusHistory()
    {
        $query = DeliveryStatus::find()
            ->joinWith(['order']);

        if (Yii::$app->request->get('id')) {
            $query->andWhere(['delivery_status.order_id' => Yii::$app->request->get('id')]);
        }

        if (Yii::$app->request->get('shop_order_number')) {
            $query->andWhere(['order.shop_order_number' => Yii::$app->request->get('shop_order_number')]);
        }

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $shopIds = $user->getAllowedShopIds();
        }

        if ($shopIds === false || count($shopIds)) {
            $query->andWhere(['IN', 'order.shop_id', $shopIds]);
        }

        $statuses = $query->orderBy(['delivery_status.status_date' => SORT_ASC])->all();

        $arrayDataProvider = new ArrayDataProvider([
            'allModels'  => $statuses,
            'pagination' => [
                'class'         => Pagination::className(),
                'pageSizeLimit' => [1, count($statuses)],
                'pageSize'      => count($statuses),
            ],
        ]);

        return $arrayDataProvider;
    }

    /**
     * Получение списка ID заказов по входящим параметрам
     *
     * @return ArrayDataProvider
     */
    public function actionIdList()
    {
        $orders = $this->getOrdersQuery(Yii::$app->request->get())->all();
        $result = [];
        foreach ($orders as $key => $order) {
            $result[] = $order->id;
        }

        $arrayDataProvider = new ArrayDataProvider([
            'allModels'  => $result,
            'pagination' => [
                'class'         => Pagination::className(),
                'pageSizeLimit' => [1, count($result)],
                'pageSize'      => count($result),
            ],
        ]);

        return $arrayDataProvider;
    }

    /**
     * Получение списка заказов по входящим параметрам
     *
     * @return ArrayDataProvider
     */
    public function actionOrderList()
    {
        $orders = $this->getOrdersQuery(Yii::$app->request->get())->all();
        $result = [];
        foreach ($orders as $key => $order) {
            $order->setApiViewScenario();
            $result[] = $order;
        }

        $arrayDataProvider = new ArrayDataProvider([
            'allModels'  => $result,
            'pagination' => [
                'class'         => Pagination::className(),
                'pageSizeLimit' => [1, count($result)],
                'pageSize'      => count($result),
            ],
        ]);

        return $arrayDataProvider;
    }

    /**
     * @param array $params
     * @return ActiveQuery
     */
    private function getOrdersQuery(array $params): ActiveQuery
    {
        $query = Order::find()->joinWith(['shop']);

        foreach ($params as $key => $param) {
            switch ($key) {
                case 'phone':
                    $query->andWhere(['LIKE', 'order.phone', $param]);
                    break;
                case 'shop_name':
                    $query->andWhere(['LIKE', 'shop.name', $param]);
                    break;
                case 'url':
                    $query->andWhere(['LIKE', 'shop.url', $param]);
                    break;
                case 'shop_order_number':
                    $query->andWhere(['LIKE', 'order.shop_order_number', $param]);
                    break;
                case 'fio':
                    $query->andWhere(['LIKE', 'order.fio', $param]);
                    break;
                case 'email':
                    $query->andWhere(['LIKE', 'order.email', $param]);
                    break;
                case 'order_id':
                    $query->andWhere(['order.id' => explode(',', $param)]);
                    break;
                case 'shop_id':
                    $query->andWhere(['order.shop_id' => explode(',', $param)]);
                    break;
            }
        }

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $shopIds = $user->getAllowedShopIds();
        }

        if (!empty($shopIds) && $shopIds !== false) {
            $query->andWhere(['IN', 'order.shop_id', $shopIds]);
        }

        return $query;
    }

    /**
     * Получение информации о заказе
     *
     * @param $id
     * @return null|static
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $shopId = (int)Yii::$app->request->get('shop_id');

        /** @var User $user */
        $user           = Yii::$app->user->identity;
        $allowedShopIds = $user->getAllowedShopIds();
        if ($shopId && !$user->isShopAvailableForUser($shopId)) {
            throw new BadRequestHttpException(Yii::t('app', 'Shop is not allowed for this user'));
        }

        $query = Order::find()->where(['id' => $id]);
        if ($shopId) {
            $query->andWhere(['shop_id' => $shopId]);
        } elseif (!empty($allowedShopIds)) {
            $query->andWhere(['IN', 'shop_id', $allowedShopIds]);
        }
        $order = $query->one();
        if ($order === null) {
            throw new NotFoundHttpException(Yii::t('app', 'Заказ с id {id} не найден', ['id' => $id]));
        }

        $order->setApiViewScenario();

        return $order;
    }

    /**
     * Отслеживание заказа
     *
     * @return array
     */
    public function actionTracking()
    {
        Yii::$app->response->format = Response::FORMAT_JSONP;
        $tracker                    = new Tracker();

        $tracker->load(Yii::$app->request->get());
        if (!$tracker->validate()) {
            Yii::$app->response->format     = Response::FORMAT_JSON;
            Yii::$app->response->statusCode = 500;
            return $tracker->errors;
        }

        $query = DeliveryStatus::find()
            ->joinWith(['order']);

        if (Yii::$app->request->get('number')) {
            $query->andWhere(['delivery_status.order_id' => $tracker->getNumber()]);
        }

        $statuses = $query->orderBy(['delivery_status.status_date' => SORT_ASC])->all();

        return [
            'callback' => $tracker->getCallback(),
            'data'     => $statuses
        ];
    }

    /**
     * Перевод заказа в статус
     *
     * @param int $id
     * @return array
     */
    public function actionSetStatus(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $status   = Yii::$app->request->post()['status'];
        $order    = Order::find()->where(['id' => $id])->one();
        $error    = [];
        $response = [];

        if (!$order) {
            Yii::$app->response->setStatusCode(404);
            $error['order'] = Yii::t('app', 'Not found');
        }

        /** @var User $user */
        $user           = Yii::$app->user->identity;
        if ($order && !$user->isShopAvailableForUser($order->shop_id)) {
            Yii::$app->response->setStatusCode(404);
            $error['order'] = Yii::t('app', 'Not found');
        }

        if ($order && !in_array((new Order())->getWorkflowStatusId($status), Order::STATUS_CONVERTABLE)) {
            Yii::$app->response->setStatusCode(400);
            $error['order'] = Yii::t('order', 'Incorrect status');
        }

        if ($order && $order->status == (new Order())->getWorkflowStatusId($status)) {
            $response['success'] = true;
        }

        if (empty($error) && empty($response)) {
            try {
                if ($order->sendToStatus($status)) {
                    $order->status = $status;
                    $order->save();
                    $response['success'] = true;
                }
            } catch (WorkflowException $e) {
                Yii::$app->response->setStatusCode(400);
                $error['status'] = Yii::t('order', 'You can not convert the order of the status of {statusFrom} to {statusTo} status', [
                    'statusFrom' => $order->getWorkflowStatusKey($order->status),
                    'statusTo'   => $status
                ]);
            } catch (\Exception $e) {
                Yii::$app->response->setStatusCode(500);
                $error['order'] = Yii::t('order', 'Order set status error');
            }
        }

        if (!empty($error)) {
            return [
                'error' => $error
            ];
        }

        return $response;
    }

    /**
     * Обновдение метода оплаты в заказе
     * (Доступно только в статусе created и confirmed)
     *
     * @param int $id
     * @return array
     */
    public function actionSetPaymentMethod(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $paymentMethod   = Yii::$app->request->post()['paymentMethod'];
        $order = Order::find()->where(['id' => $id])->one();

        /** @var User $user */
        $user = Yii::$app->user->identity;

        if (!$order) {
            return $this->error(404, ['order' => Yii::t('order', 'Not found')]);
        }

        if (!isset($order->getPaymentMethods()[$paymentMethod])) {
            return $this->error(400, ['order' => Yii::t('order', 'Not a suitable method of payment')]);
        }

        if (!in_array($order->status, Order::STATUS_PAYMENT_CHANGABLE)) {
            return $this->error(400, ['order' => Yii::t('order', 'Not editable order status')]);
        }

        if (empty($order->shop->option->can_change_payment_method)
            || !$user->isShopAvailableForUser($order->shop_id)
        ) {
            return $this->error(403, ['order' => Yii::t('order', 'You can not change payment method')]);
        }

        $order->payment_method = $paymentMethod;
        return [
            'success' => $order->save()
        ];
    }

    /**
     * @param int $code
     * @param array $errors
     * @return array
     */
    public function error(int $code, array $errors): array
    {
        Yii::$app->response->setStatusCode($code);
        return [
            'error' => $errors
        ];
    }


    /**
     * Указание габаритов заказа
     *
     * @return array
     */
    public function actionSetDimensions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order = Order::findOne(Yii::$app->request->post('id', 0));

        if (!$order) {
            Yii::$app->response->setStatusCode(404);
            $error['order'][] = Yii::t('app', 'Not found');
            return [
                'errors' => $error
            ];
        }

        /** @var User $user */
        $user           = Yii::$app->user->identity;
        if ($order && !$user->isShopAvailableForUser($order->shop_id)) {
            Yii::$app->response->setStatusCode(403);
            $error['order'][] = Yii::t('app', 'Order not allowed for this user');
            return [
                'errors' => $error
            ];
        }

        if (!in_array($order->getWorkflowStatusKey($order->status), [
            Order::STATUS_CREATED,
            Order::STATUS_IN_COLLECTING,
            Order::STATUS_CONFIRMED
        ])) {
            Yii::$app->response->setStatusCode(403);
            $error['order'][] = Yii::t('app', 'Order not in editable status');
            return [
                'errors' => $error
            ];
        }

        $post = Yii::$app->request->post();
        $dimension = new api\model\Order\Dimension();
        if ($dimension->load($post, 'dimensions') && $dimension->validate()) {
            $order->width = $dimension->width;
            $order->height = $dimension->height;
            $order->length = $dimension->length;

            $result["success"] = $order->save();
            if (!empty($order->errors)) {
                Yii::$app->response->setStatusCode(400);
                $result['errors'] = $order->errors;
            }
            return $result;
        } else {
            Yii::$app->response->setStatusCode(400);
            return [
                'errors' => $dimension->errors
            ];
        }
    }
}