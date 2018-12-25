<?php
namespace app\delivery;

use app\models\Common\Calculator;
use app\models\Courier;
use app\models\Order;
use app\models\OrderDelivery;
use yii\httpclient\Request;
use yii\httpclient\Response;

interface DeliveryInterface
{

    /**
     * @return Request|bool
     */
    public function getDeliveryRequest();

    /**
     * @param Response $response
     * @param OrderDelivery $orderDeliveryModel
     * @return bool|Request
     */
    public function getCalculatorDeliveries(Response $response, OrderDelivery $orderDeliveryModel);

    /**
     * @param $orderIds array
     * @return mixed
     */
    public function getLabels($orderIds);
    public function call();

    public function getRegistries();

    /**
     * @return Response
     */
    public function createOrder();
    public function updateOrder();
    public function reSendOrder();

    /**
     * @param $orderId int
     * @return mixed
     */
    public function getStatusesHistory($orderId);

    /**
     * @param int $orderId
     * @return mixed
     */
    public function getCurrentStatus($orderId);

    /**
     * @param $orderIds array
     * @return mixed
     */
    public function getLastStatuses($orderIds);

    public function setOrder(Order $order);
    public function setCalculator(Calculator $calculator);
    public function setCourier(Courier $courier);

    /**
     * @return Order
     */
    public function getOrder();

    /**
     * @return Courier
     */
    public function getCourier();
}