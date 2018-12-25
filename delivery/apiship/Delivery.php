<?php
namespace app\delivery\apiship;

use app\delivery\apiship\calculator\Calculate;
use app\delivery\apiship\calculator\Calculator;
use app\delivery\apiship\Courier as ApishipCourier;
use app\delivery\apiship\orders\Orders;
use app\delivery\DeliveryHelper;
use app\delivery\DeliveryInterface;
use app\exception\CourierProviderException;
use app\exception\CourierRequestException;
use app\models\Courier;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Points;
use app\models\Tariff;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\httpclient\Request;
use yii\httpclient\Response;

class Delivery extends Component implements DeliveryInterface
{
    const PICKUP_TYPE_FROM_DOOR = 1;
    const PICKUP_TYPE_ON_TERMINAL = 2;

    const DELIVERY_TO_DOOR = 1;
    const DELIVERY_TO_POINT = 2;

    const POINT_TYPE_PVZ = 1;
    const POINT_TYPE_POSTAMAT = 2;
    const POINT_TYPE_MAIL = 3;
    const POINT_TYPE_TERMINAL = 4;

    // Утилитарные тарифы которые не должны участвовать в расчетах
    const JUNK_TARIFFS = [
        173,
        245, // Тариф OnTime - Возврат Отправлений
    ];

    /** @var array */
    private static $pickupTypes = [
        self::PICKUP_TYPE_FROM_DOOR => OrderDelivery::PICKUP_TYPE_FROM_DOOR,
        self::PICKUP_TYPE_ON_TERMINAL => OrderDelivery::PICKUP_TYPE_ON_TERMINAL,
    ];
    /** @var array */
    private static $pointTypes = [
        self::POINT_TYPE_PVZ => OrderDelivery::POINT_TYPE_PVZ,
        self::POINT_TYPE_POSTAMAT => OrderDelivery::POINT_TYPE_POSTAMAT,
        self::POINT_TYPE_MAIL => OrderDelivery::POINT_TYPE_MAIL,
        self::POINT_TYPE_TERMINAL => OrderDelivery::POINT_TYPE_TERMINAL,
    ];
    /** @var array */
    private static $deliveryTypes = [
        self::DELIVERY_TO_DOOR => OrderDelivery::DELIVERY_TO_DOOR,
        self::DELIVERY_TO_POINT => OrderDelivery::DELIVERY_TO_POINT
    ];
    private $_token;
    /** @var Order */
    private $_order;
    /** @var \app\models\Common\Calculator */
    private $_calculator;
    /** @var Courier */
    private $_courier;
    /** @var Points[] $points */
    private $_points;

    /**
     * @param $name
     * @return int|string
     */
    public static function getPickupTypeByTypeName($name)
    {
        foreach (self::$pickupTypes as $type => $typeName) {
            if ($typeName === $name) {
                return $type;
            }
        }

        return $name;
    }

    /**
     * @param $name
     * @return int|string
     */
    public static function getDeliveryTypeByDeliveryTypeName($name)
    {
        foreach (self::$deliveryTypes as $type => $typeName) {
            if ($typeName === $name) {
                return $type;
            }
        }

        // TODO исправить это непотребство
        if($name == 'mail') {
            return self::DELIVERY_TO_DOOR;
        }

        return $name;
    }

    /**
     * @return Request
     */
    public function getDeliveryRequest()
    {
        /** @var Calculate $calculatorService */
        $calculatorService = Yii::createObject(['class' => Calculate::className(), 'token' => $this->getToken()], []);
        $calculatorService->prepare($this->_calculator);
        return $calculatorService->getRequest();
    }

    /**
     * @deprecated
     * @param Response $response
     * @param OrderDelivery $orderDeliveryModel
     * @return array
     */
    public function getCalculatorDeliveries(Response $response, OrderDelivery $orderDeliveryModel)
    {
        $orderDeliveries = [];
        // Получение возможных доставок курьером
        if ($this->_calculator->getAllowedTypes()[OrderDelivery::DELIVERY_TO_DOOR]) {
            $orderDeliveries = array_merge(
                $orderDeliveries,
                $this->getCalculatedDelivery(
                    ArrayHelper::getValue(
                        $response->data,
                        'deliveryToDoor',
                        []
                    ),
                    OrderDelivery::DELIVERY_TO_DOOR,
                    $orderDeliveryModel
                )
            );
        }

        // Получение возможных доставок почтой
        if ($this->_calculator->getAllowedTypes()[OrderDelivery::DELIVERY_POST]) {
            $orderDeliveries = array_merge(
                $orderDeliveries,
                $this->getCalculatedDelivery(
                    ArrayHelper::getValue(
                        $response->data,
                        'deliveryToDoor',
                        []
                    ),
                    OrderDelivery::DELIVERY_POST,
                    $orderDeliveryModel
                )
            );
        }

        // Получение возможных доставок в ПВЗ
        if ($this->_calculator->getAllowedTypes()[OrderDelivery::DELIVERY_TO_POINT]) {
            $orderDeliveries = array_merge(
                $orderDeliveries,
                $this->getCalculatedDelivery(
                    ArrayHelper::getValue(
                        $response->data,
                        'deliveryToPoint',
                        []
                    ),
                    OrderDelivery::DELIVERY_TO_POINT,
                    $orderDeliveryModel
                )
            );
        }

        return $orderDeliveries;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        if (!$this->_token) {
            $response = (new Users())->login();
            $this->_token = ArrayHelper::getValue($response->data, 'accessToken');
        }

        return $this->_token;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->_order = $order;
    }

    /**
     * @param \app\models\Common\Calculator $calculator
     */
    public function setCalculator(\app\models\Common\Calculator $calculator)
    {
        $this->_calculator = $calculator;
    }

    /**
     * Получение доставки
     *
     * @param $deliveries array
     * @param $deliveryType string
     * @param OrderDelivery $orderDeliveryModel
     * @return array
     */
    private function getCalculatedDelivery($deliveries, $deliveryType, OrderDelivery $orderDeliveryModel)
    {
        $itemCounter = 0;
        $orderDeliveries = [];
        $pointArray = [];

        if (!$this->_calculator->getToAddress()) {
            return [];
        }

        foreach ($deliveries as $provider) {
            if (is_array($provider['tariffs']) && !empty($provider['tariffs'])) {
                foreach ($provider['tariffs'] as $tarif) {
                    // Хак для пропуска почтовых тарифов
                    // TODO что то придумать
                    if (in_array($tarif['tariffId'], array_keys(DeliveryHelper::$mailTariffMapper))
                        && $deliveryType == OrderDelivery::DELIVERY_TO_DOOR
                    ) {
                        continue;
                    }

                    if (!in_array($tarif['tariffId'], array_keys(DeliveryHelper::$mailTariffMapper))
                        && $deliveryType == OrderDelivery::DELIVERY_POST
                    ) {
                        continue;
                    }

                    if (in_array($tarif['tariffId'], self::JUNK_TARIFFS)) continue;

                    if ($provider['providerKey'] == DeliveryHelper::CARRIER_CODE_CDEK
                        && !in_array($tarif['tariffProviderId'], DeliveryHelper::$сdekTariffs)
                    ) {
                        continue;
                    }

                    if ($points = ArrayHelper::getValue($tarif, 'pointIds', [1])) {
                        foreach ($points as $i => $point) {

                            if (($provider['providerKey'] == DeliveryHelper::CARRIER_CODE_B2CPL
                                || $provider['providerKey'] == DeliveryHelper::CARRIER_CODE_BOXBERRY)
                                && $this->_calculator->payment_method == Order::PAYMENT_METHOD_DELIVERY_PAY
                            ) {
                                continue;
                            }

                            $orderDelivery = clone $orderDeliveryModel;
                            $orderDelivery->tariff_id = $tarif['tariffId'];
                            $orderDelivery->name = $tarif['tariffName'];
                            $orderDelivery->type = $deliveryType;
                            $orderDelivery->carrier_key = $provider['providerKey'];

                            $cost = (float) str_replace(',', '.', $tarif['deliveryCost']);
                            $orderDelivery->charge = Tariff::getFasteryCharge($cost);
                            $orderDelivery->original_cost = number_format(
                                ($cost + $orderDelivery->charge), 0, '.', ''
                            );

                            // Получим цену с учетом пользовательской тарификации
                            $cost = Tariff::getPersonalTariffCost(
                                $orderDelivery->original_cost,
                                $this->_calculator->cost,
                                $this->_calculator->shop_id,
                                $this->_calculator->weight,
                                $this->_calculator->getToAddress()->getCityFiasId() ?? $this->_calculator->getToAddress()->getCity(),
                                $orderDelivery->carrier_key,
                                $orderDelivery->type
                            );

                            // Округляем цену в соответсвтии с настройками пользователя
                            $orderDelivery->cost = Tariff::getRoundedDeliveryCost(
                                $cost,
                                $this->_calculator->getRoundingOff(),
                                $this->_calculator->getRoundingOffPrefix()
                            );

                            $orderDelivery->min_term = (int) ($tarif['daysMin'] + $this->_calculator->getProccessDayCount());
                            $orderDelivery->max_term = (int) ($tarif['daysMax'] + $this->_calculator->getProccessDayCount());
                            $orderDelivery->city = $this->_calculator->getFromAddress()->getCity();

                            $types = [];
                            foreach ($tarif['pickupTypes'] as $type) {
                                if (isset(self::$pickupTypes[$type])) {
                                    $types[] = self::$pickupTypes[$type];
                                }
                            }

                            $orderDelivery->pickup_types = $types;
                            $orderDelivery->class_name_provider = self::className();

                            if ($deliveryType == OrderDelivery::DELIVERY_TO_POINT) {
                                $orderDelivery->point_id = $point;
                                $pointAdditional = $this->getPointAdditional($orderDelivery);
                                $point_type = ArrayHelper::getValue($pointAdditional, 'type');
                                $availableOperations = ArrayHelper::getValue($pointAdditional, 'available_operation');
                                $orderDelivery->point_type = ArrayHelper::getValue(self::$pointTypes, $point_type);
                                $orderDelivery->point_address = ArrayHelper::getValue($pointAdditional, 'address');
                                $orderDelivery->phone = ArrayHelper::getValue($pointAdditional, 'phone');
                                $orderDelivery->lat = ArrayHelper::getValue($pointAdditional, 'lat');
                                $orderDelivery->lng = ArrayHelper::getValue($pointAdditional, 'lng');

                                // Если адрес ПВЗ не совпадает с адресом запроса пропускаем данный ПВЗ
                                if($this->_calculator->getToAddress()->getCityFiasId()
                                    && $this->_calculator->getToAddress()->getCityFiasId() != ArrayHelper::getValue($pointAdditional, 'city_guid')
                                ) {
                                    continue;
                                }

                                // Проверим наличие данного ПВЗ в списке (исключение дублей)
                                $doublePointKey = $orderDelivery->point_address . '_'  . $orderDelivery->carrier_key;
                                if (!isset($pointArray[$doublePointKey])) {
                                    $pointArray[$doublePointKey] = [
                                        'counter' =>  $itemCounter,
                                        'cost' => $orderDelivery->cost
                                    ];
                                } else {
                                    if ($pointArray[$doublePointKey]['cost'] > $orderDelivery->cost) {
                                        unset($orderDeliveries[$pointArray[$doublePointKey]['counter']]);
                                        $pointArray[$doublePointKey] = [
                                            'counter' =>  $itemCounter,
                                            'cost' => $orderDelivery->cost
                                        ];
                                    } else {
                                        continue;
                                    }
                                }

                                // Добавляем в список только те ПВЗ которые имееют возможность выдачи заказов
                                if ($orderDelivery->point_address !== null
                                    && $availableOperations > 1
                                ) {
                                    $orderDeliveries[$itemCounter] = $orderDelivery;
                                    $itemCounter++;
                                }
                            } else {
                                $orderDeliveries[$itemCounter] = $orderDelivery;
                                $itemCounter++;
                            }
                        }
                    }

                }
            }
        }

        return $orderDeliveries;
    }

    /**
     * @param OrderDelivery $orderDelivery
     * @return array|mixed
     */
    private function getPointAdditional(OrderDelivery $orderDelivery)
    {
        $points = $this->getPoints();

        if ($points) {
            return ArrayHelper::getValue($points, $orderDelivery->point_id, []);
        }

        return [];
    }

    /**
     * @return Points[]|mixed
     */
    public function getPoints()
    {
        if (!$this->_points) {
            $className = self::className();
            /** @var Points[] $points */
            $this->_points = Yii::$app->cache->getOrSet([md5($className), 'points'], function () use ($className){
                $points = Points::find()->select(['point_id', 'address', 'phone', 'lat', 'lng', 'type', 'city_guid', 'available_operation'])->where(['class_name' => $className])->asArray()->all();
                return ArrayHelper::map($points, 'point_id', function ($row) {
                    unset($row['point_id']);
                    return $row;
                });
            }, 3600);
        }

        return $this->_points;
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function getListPoints($options = ['limit' => 200])
    {
        /** @var Lists $listMethods */
        $listMethods = Yii::createObject(['class' => Lists::className(), 'token' => $this->getToken()]);
        $response = $listMethods->points($options);

        return $response->data['rows'];
    }

    /**
     * @param string $carrierKey
     * @return mixed
     */
    public function getListServices(string $carrierKey)
    {
        if (!$carrierKey) {
            return false;
        }

        $option = [
            'providerKey' => $carrierKey
        ];

        /** @var Lists $listMethods */
        $listMethods = Yii::createObject(['class' => Lists::className(), 'token' => $this->getToken()]);
        $response = $listMethods->services($option);

        return $response->data;
    }

    /**
     * @return mixed
     */
    public function getPickPointCities()
    {

        $option = [
            'limit' => 99999
        ];

        /** @var Lists $listMethods */
        $listMethods = Yii::createObject(['class' => Lists::className(), 'token' => $this->getToken()]);
        $response = $listMethods->pickPointCities($option);

        return $response->data;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function createOrder()
    {
        $order = $this->getOrder();
        /** @var Orders $orderSrvice */
        $orderSrvice = Yii::createObject(['class' => Orders::className(), 'token' => $this->getToken()], [$order]);
        $response = $orderSrvice->create();

        if (isset($response->data['orderId'])) {
            (new Query())->createCommand()->update(
                '{{%order}}',
                ['provider_number' => $response->data['orderId']],
                ['id' => $order->id]
            )->execute();
        } elseif (isset($response->data['errors'])) {
            $messages = '';
            foreach ($response->data['errors'] as $error) {
                $messages .= '<strong>' . $error['message'] . '</strong><br/>';
            }
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new Exception('Ошибка создания заказа в Службе Доставки<br/>' . $messages);
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function updateOrder()
    {
        $order = $this->getOrder();
        /** @var Orders $orderSrvice */
        $orderSrvice = Yii::createObject(['class' => Orders::className(), 'token' => $this->getToken()], [$order]);
        $response = $orderSrvice->update();

        if (isset($response->data['orderId'])) {
            (new Query())->createCommand()->update(
                '{{%order}}',
                ['provider_number' => $response->data['orderId']],
                ['id' => $order->id]
            )->execute();
        } elseif (isset($response->data['errors'])) {
            $messages = '';
            foreach ($response->data['errors'] as $error) {
                $messages .= '<strong>' . $error['message'] . '</strong><br/>';
            }
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new Exception('Ошибка обновления заказа в Службе Доставки<br/>' . $messages);
        }

        return true;
    }

    /**
     * @param array $orderIds
     * @return bool
     * @throws Exception
     */
    public function getLabels($orderIds)
    {
        /**
         * @var  $orderKey
         * @var  $order Order
         */
        foreach ($orderIds as $orderKey => $order) {
            /** @var PrintForm $printForm */
            $printForm = Yii::createObject(['class' => PrintForm::className(), 'token' => $this->getToken(), 'orderIds' => [$orderKey]]);
            $response = $printForm->getLabels();
            if (isset($response->data) && ($labelUrl = ArrayHelper::getValue($response->data, 'url'))) {
                (new Query())->createCommand()->update('{{%order}}', ['label_url' => $labelUrl], ['id' => $order->id])->execute();
            } else {
                Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
                throw new Exception(Yii::t('app', 'Order translated to the error status. System has processing error automatically.'));
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function reSendOrder()
    {
        $order = $this->getOrder();
        /** @var Orders $orderSrvice */
        $orderSrvice = Yii::createObject(['class' => Orders::className(), 'token' => $this->getToken()], [$order]);
        $response = $orderSrvice->reSend();

        if (isset($response->data['orderId'])) {
            // Тут делать нечего не нужно
        } elseif (isset($response->data['errors'])) {
            $messages = '';
            foreach ($response->data['errors'] as $error) {
                $messages .= '<strong>' . $error['message'] . '</strong><br/>';
            }
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new Exception('Ошибка обновления заказа в Службе Доставки<br/>' . $messages);
        }

        return true;
    }


    /**
     * @param array $orderIds
     * @return null|string
     * @throws Exception
     */
    public function getLabelList(array $orderIds): ?string
    {
        /** @var PrintForm $printForm */
        $printForm = Yii::createObject(['class' => PrintForm::className(), 'token' => $this->getToken(), 'orderIds' => $orderIds]);
        $response = $printForm->getLabels();
        if (isset($response->data) && ($labelUrl = ArrayHelper::getValue($response->data, 'url'))) {
            return $labelUrl;
        } else {
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new Exception(Yii::t('app', 'Order translated to the error status. System has processing error automatically.'));
        }
    }

    /**
     * Вызов курьера
     * @return mixed
     * @throws CourierProviderException
     * @throws CourierRequestException
     */
    public function call()
    {
        $courier = $this->getCourier();
        /** @var ApishipCourier $apishipCourier */
        $apishipCourier = Yii::createObject(['class' => ApishipCourier::className(), 'token' => $this->getToken()], [$courier]);
        $response = $apishipCourier->call();

        if (isset($response->data) && ($id = ArrayHelper::getValue($response->data, 'id'))) {
            return $id;
        } elseif (isset($response->data['errors']) || isset($response->data['code'])) {
            $errors = [];
            if (isset($response->data['errors'])) {
                $errors = $response->data['errors'];
            }
            if (isset($response->data['code'])) {
                $errors = [$response->data];
            }
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = sprintf(
                    '<strong>%s %s</strong>',
                    $error['message'],
                    (isset($error['description']) && $error['description']) != '' ? ': ' . $error['description'] : ''
                );
            }
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new CourierProviderException('Ошибка вызова курьера в Службе Доставки<br/>' . implode('<br/>', $messages));
        } else {
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new CourierRequestException('Ошибка выполнения запроса на вызова курьера в Службу Доставки');
        }

    }

    /**
     * @return Courier
     */
    public function getCourier()
    {
        return $this->_courier;
    }

    /**
     * @param Courier $courier
     */
    public function setCourier(Courier $courier)
    {
        $this->_courier = $courier;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getRegistries()
    {
        $courier = $this->getCourier();
        $orderIds = $courier->getOrdersProviredIds();
        /** @var PrintForm $printForm */
        $printForm = Yii::createObject(['class' => PrintForm::className(), 'token' => $this->getToken(), 'orderIds' => $orderIds]);
        $response = $printForm->getRegistries();

        if (isset($response->data) && ($item = ArrayHelper::getValue($response->data, 'waybillItems.0'))) {
            return (new Query())->createCommand()->update('{{%courier}}', ['registry_label_url' => $item['file']], ['id' => $courier->id])->execute();
        } elseif (isset($response->data['failedOrders']) && $response->data['failedOrders'] !== null) {
            $messages = '';
            foreach ($response->data['failedOrders'] as $failedOrder) {
                $messages .= '<strong>' . $failedOrder['message'] . '</strong><br/>';
            }
            throw new Exception(Yii::t('app', 'Не удалось получить печатную форму реестра<br/>' . $messages));
        } else {
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new Exception(Yii::t('app', 'Ошибка запроса на получение печатной формы реестра'));
        }
    }

    /**
     * @param array $orderIds
     * @return mixed|null
     * @throws Exception
     */
    public function getLastStatuses($orderIds)
    {
        /** @var Status $apishipStatus */
        $apishipStatus = Yii::createObject(['class' => Status::className(), 'token' => $this->getToken(), 'orderIds' => array_keys($orderIds)]);
        $response = $apishipStatus->lastStatuses();

        if (isset($response->data)) {
            return $response->data;
        } else {
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new Exception(Yii::t('app', 'Ошибка запроса на статусов заказов'));
        }
    }

    /**
     * @param array $orderId
     * @return mixed
     * @throws Exception
     */
    public function getStatusesHistory($orderId)
    {
        /** @var Status $apishipStatus */
        $apishipStatus = Yii::createObject(['class' => Status::className(), 'token' => $this->getToken(), 'orderId' => $orderId]);
        $response = $apishipStatus->statusHistory();

        if (isset($response->data)) {
            return $response->data;
        } else {
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new Exception(Yii::t('app', 'Ошибка запроса на статусов заказов'));
        }
    }

    /**
     * @param int $orderId
     * @return mixed
     * @throws Exception
     */
    public function getCurrentStatus($orderId)
    {
        /** @var Status $apishipStatus */
        $apishipStatus = Yii::createObject(['class' => Status::className(), 'token' => $this->getToken(), 'orderId' => $orderId]);
        $response = $apishipStatus->currentStatus();

        if (isset($response->data)) {
            return $response->data;
        } else {
            Yii::error("Error in request: \n" . print_r($response, true), __METHOD__);
            throw new Exception(Yii::t('app', 'Ошибка запроса на статусов заказов'));
        }
    }
}