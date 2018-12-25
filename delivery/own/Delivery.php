<?php
namespace app\delivery\own;

use app\delivery\DeliveryInterface;
use app\delivery\own\Calculator\Calculate;
use app\delivery\own\Calculator\Calculator;
use app\models\Courier;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Shop;
use app\models\Tariff;
use Yii;
use yii\base\Component;
use yii\httpclient\Request;
use yii\httpclient\Response;

class Delivery extends Component implements DeliveryInterface
{
    /** @var Order */
    private $_order;
    /** @var \app\models\Common\Calculator */
    private $_calculator;

    /**
     * @return Request|bool
     */
    public function getDeliveryRequest()
    {
        return false;
    }

    /**
     * Получение доставки
     * @param $deliveries array
     * @param $deliveryType string
     * @param OrderDelivery $orderDeliveryModel
     * @return array
     */
    private function getOrderDelivery($deliveries, $deliveryType, OrderDelivery $orderDeliveryModel)
    {
        $orderDeliveries = [];
        $shop = Shop::findOne($this->_calculator->shop_id);
        foreach ($deliveries as $delivery) {
            if ($delivery['type'] != $deliveryType) {
                continue;
            }
            $orderDelivery = clone $orderDeliveryModel;
            $orderDelivery->tariff_id = $delivery['id'];
            $orderDelivery->name = $delivery['name'];
            $orderDelivery->type = $delivery['type'];
            $orderDelivery->carrier_key = 'own';
            $orderDelivery->original_cost = $delivery['inventories'][0]['cost'];

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
                $shop ? $shop->rounding_off : 1,
                $shop ? $shop->rounding_off_prefix: 0
            );
            // $orderDelivery->cost = $delivery['inventories'][0]['cost'];
            $orderDelivery->min_term = (int) $delivery['min_term'];
            $orderDelivery->max_term = (int) $delivery['max_term'];
            $orderDelivery->city = 'Москва';
            $orderDelivery->class_name_provider = self::className();
            $orderDelivery->pickup_types = ['on_terminal'];

            if ($deliveryType == OrderDelivery::DELIVERY_TO_POINT) {
                $orderDelivery->point_id = $shop->defaultWarehouse->id;
                $orderDelivery->point_address = $delivery['address']['full_address'];
                $orderDelivery->phone = $shop->phone;
                $orderDelivery->lat = $delivery['address']['lat'];
                $orderDelivery->lng = $delivery['address']['lng'];
                $orderDelivery->point_type = OrderDelivery::POINT_TYPE_PVZ;
            }

            $orderDeliveries[] = $orderDelivery;
        }

        return $orderDeliveries;
    }

    /**
     * @param Response $response
     * @param OrderDelivery $orderDeliveryModel
     * @return array
     */
    public function getCalculatorDeliveries(Response $response, OrderDelivery $orderDeliveryModel)
    {
        $orderDeliveries = [];

        /** @var Calculate $calculatorService */
        $calculatorService = Yii::createObject(['class' => Calculate::className()], []);
        $calculatorService->prepare($this->_calculator);
        $calculation = $calculatorService->calculate();

        if (!empty($calculation)) {
            $orderDeliveries = array_merge(
                $orderDeliveries,
                $this->getOrderDelivery(
                    $calculation,
                    OrderDelivery::DELIVERY_TO_DOOR,
                    $orderDeliveryModel
                )
            );

            $orderDeliveries = array_merge(
                $orderDeliveries,
                $this->getOrderDelivery(
                    $calculation,
                    OrderDelivery::DELIVERY_TO_POINT,
                    $orderDeliveryModel
                )
            );
        }

        return $orderDeliveries;
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
     * @return mixed|void
     */
    public function setCalculator(\app\models\Common\Calculator $calculator)
    {
        $this->_calculator = $calculator;
    }

    /**
     * @param $orderIds array
     * @return mixed
     */
    public function getLabels($orderIds)
    {
        // TODO: Implement getLabels() method.
    }

    public function call()
    {
        // TODO: Implement call() method.
    }

    public function getRegistries()
    {
        // TODO: Implement getRegistries() method.
    }

    /**
     * @return Response
     */
    public function createOrder()
    {
        // TODO: Implement createOrder() method.
    }

    public function updateOrder()
    {
        // TODO: Implement updateOrder() method.
    }

    /**
     * @param $orderId array
     * @return mixed
     */
    public function getStatusesHistory($orderId)
    {
        // TODO: Implement getStatusesHistory() method.
    }

    /**
     * @param int $orderId
     * @return mixed
     */
    public function getCurrentStatus($orderId)
    {
        // TODO: Implement getCurrentStatus() method.
    }

    /**
     * @param $orderIds array
     * @return mixed
     */
    public function getLastStatuses($orderIds)
    {
        // TODO: Implement getLastStatuses() method.
    }

    public function setCourier(Courier $courier)
    {
        // TODO: Implement setCourier() method.
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return Courier
     */
    public function getCourier()
    {
        // TODO: Implement getCourier() method.
    }

    public function reSendOrder()
    {
        // TODO: Implement reSendOrder() method.
    }
}