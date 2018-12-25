<?php

namespace app\delivery\apiship\orders;

use app\delivery\apiship\BaseApiShip;
use app\delivery\apiship\Delivery;
use app\delivery\DeliveryHelper;
use app\models\Common\ShopDelivery;
use app\models\DeliveryService;
use app\models\Order;
use app\models\OrderDelivery;
use Yii;

class Orders extends BaseApiShip
{
    /** @var \app\delivery\apiship\orders\OrderData */
    public $order;

    /** @var \app\delivery\apiship\orders\Cost */
    public $cost;

    /** @var \app\delivery\apiship\orders\Address */
    public $sender;

    /** @var \app\delivery\apiship\orders\Address */
    public $recipient;

    /** @var \app\delivery\apiship\orders\Item[] */
    public $items = [];

    /** @var \app\delivery\apiship\orders\Place[] */
    public $places = [];

    /** @var \app\delivery\apiship\orders\ExtraParam[] */
    public $extraParams;

    /** @var int */
    public $providerId;

    /**
     * @param Order $order
     * @param array $config
     */
    public function __construct(Order $order, $config = [])
    {
        $this->providerId = $order->provider_number;

        $this->order = new OrderData();
        $this->order->clientNumber = $order->getOrderNumber();
        $this->order->description = $order->comment;
        $this->order->providerKey = $order->delivery->carrier_key;
        $this->order->pickupType = Delivery::getPickupTypeByTypeName($order->delivery->pickup_type);
        $this->order->pickupDate = date('Y-m-d', $order->delivery->pickup_date);

        $shopDelivery = new ShopDelivery($order->shop_id, $order->delivery->carrier_key);
        $this->order->pickupTimeStart = $shopDelivery->getPickupTimeStart();
        $this->order->pickupTimeEnd = $shopDelivery->getPickupTimeEnd();

        if (empty($order->delivery->delivery_date)) {
            $order->delivery->delivery_date = strtotime($this->order->pickupDate) + ($order->delivery->min_term * 86400);
        }

        if ($order->delivery->carrier_key == DeliveryHelper::CARRIER_CODE_B2CPL && in_array(
                $order->address->city_fias_id,
                ['0c5b2444-70a0-4932-980c-b4dc0d3f02b5', 'c2deb16a-0330-4f05-821f-1d09c93331e6']
            ) || $order->delivery->carrier_key != 'b2cpl') {
            $this->order->deliveryDate = date('Y-m-d', $order->delivery->delivery_date);
            $this->order->deliveryTimeStart = $order->delivery->time_start ? date('H:i',  strtotime($order->delivery->time_start)) : OrderDelivery::MIN_DELIVERY_TIME.":00";
            $this->order->deliveryTimeEnd = $order->delivery->time_end ? date('H:i',  strtotime($order->delivery->time_end)) : OrderDelivery::MAX_DELIVERY_TIME . ":00";
        }

        $this->order->deliveryType = Delivery::getDeliveryTypeByDeliveryTypeName($order->delivery->type);
        $this->order->tariffId = $order->delivery->tariff_id;
        $this->order->weight = $order->getWeight() ? $order->getWeight() : 10;

        if ($order->width || $order->length || $order->height) {
            $this->order->width = (int) $order->width;
            $this->order->length = (int) $order->length;
            $this->order->height = (int) $order->height;
        }

        if ($this->order->deliveryType === Delivery::DELIVERY_TO_POINT) {
            $this->order->pointOutId = $order->delivery->point_id;
        }

        $this->order->pointInId = $order->delivery->pickup_point_id;

        $this->cost = new Cost();
        $this->cost->assessedCost = $order->getAssessed_cost();
        $this->cost->codCost = $order->getCodCost();

        if ($order->payment_method === Order::PAYMENT_METHOD_FULL_PAY
            || $order->payment_method === Order::PAYMENT_METHOD_DELIVERY_PAY
        ) {
            $this->cost->deliveryCost = (float) $order->delivery->cost;
        } else {
            $this->cost->deliveryCost = 0;
        }

        // Хак для B2CPL Почты России, которой требуется Оценочная стоимость не меньше наложенного платежа
        if(($this->order->providerKey == DeliveryHelper::CARRIER_CODE_B2CPL || $this->order->providerKey == DeliveryHelper::CARRIER_CODE_BOXBERRY)) {
            $this->cost->assessedCost = ($this->cost->codCost)
                ? $order->getCost(false)
                : $this->cost->assessedCost;
        }

        $this->sender = new Address();
        $this->sender->addressString = $order->warehouse->address->full_address;
        $this->sender->region = $order->warehouse->address->region;
        $this->sender->city = $order->warehouse->address->city;
        $this->sender->cityGuid = $order->warehouse->address->city_fias_id;
        $this->sender->street = $order->warehouse->address->street;
        $this->sender->house = $order->warehouse->address->house;
        $this->sender->contactName = $order->warehouse->contact_fio;
        $this->sender->companyName = $order->shop->name;
        $this->sender->phone = $order->warehouse->getClearPhone();


        $this->recipient = new Address();
        // Совет от Ильи из ApiShip
        if ($this->order->providerKey != DeliveryHelper::CARRIER_CODE_CDEK) {
            $this->recipient->addressString = $order->address->full_address;
        }
        $this->recipient->region = $order->address->region;
        $this->recipient->city = $order->address->city;
        $this->recipient->cityGuid = $order->address->city_fias_id;
        $this->recipient->street = $order->address->street;
        $this->recipient->house = $order->address->house;
        $this->recipient->contactName = $order->fio;
        $this->recipient->phone = $order->getClearPhone();

        foreach ($order->orderProducts as $orderProduct) {
            $item = new Item();
            
            if ($order->payment_method === Order::PAYMENT_METHOD_FULL_PAY
                || $order->payment_method === Order::PAYMENT_METHOD_PRODUCT_PAY
            ) {
                $item->cost = (float)$orderProduct->price;
            } else {
                $item->cost = 0;
            }

            $item->assessedCost = $orderProduct->accessed_price ?? $item->cost;
            if (($this->order->providerKey == DeliveryHelper::CARRIER_CODE_B2CPL
                    || $this->order->providerKey == DeliveryHelper::CARRIER_CODE_BOXBERRY)
                && ($order->payment_method === Order::PAYMENT_METHOD_FULL_PAY
                    || $order->payment_method === Order::PAYMENT_METHOD_PRODUCT_PAY)
            ) {
                $item->assessedCost = $item->cost;
            }
            $item->quantity = (int)$orderProduct->quantity;
            $item->weight = $orderProduct->weight
                ? (int) $orderProduct->weight
                : 10 / $order->getProductsCount();
            $item->width = $orderProduct->width ? (int) $orderProduct->width : 1;
            $item->length = $orderProduct->length ? (int) $orderProduct->length : 1;
            $item->height = $orderProduct->height ? (int) $orderProduct->height : 1;
            $item->articul = $orderProduct->product->barcode
                ? $orderProduct->product->barcode
                : (string) $orderProduct->product->id;
            $item->description = $orderProduct->name;

            $this->items[] = $item;
        }

        if ($order->delivery->isPartial()) {
            $extraParam = new ExtraParam();
            $extraParam->key = $order->delivery->getServiceCode(
                $this->order->providerKey,
                DeliveryService::SERVICE_PARTIAL
            );
            $extraParam->value = DeliveryService::EXTRA_PARAM_ADD_VALUE;
            $this->extraParams[] = $extraParam;
        }

        if (isset(DeliveryService::DELIVERY_SERVICE_KEY_MAP[$order->delivery->carrier_key])) {
            foreach (DeliveryService::DELIVERY_SERVICE_KEY_MAP[$order->delivery->carrier_key] as $serviceCode => $service) {
                if (isset($service[$order->shop_id])) {
                    $extraParam = new ExtraParam();
                    $extraParam->key = $order->delivery->getServiceCode(
                        $this->order->providerKey,
                        $serviceCode
                    );
                    $extraParam->value = $service[$order->shop_id];
                    $this->extraParams[] = $extraParam;
                }
            }
        }

        // Требование IML передовать в комментарии названия магазина для идентификации отправления
        if ($order->delivery->carrier_key == DeliveryHelper::CARRIER_CODE_IML) {
            $this->order->description = $order->shop->name;
        }

        parent::__construct($config);
    }

    /**
     * @return \yii\httpclient\Response
     */
    public function create()
    {
        return $this->sendRequest($this->getArr($this), 'orders', 'post', false);
    }

    /**
     * @return \yii\httpclient\Response
     */
    public function update()
    {
        return $this->sendRequest($this->getArr($this), 'orders/' . $this->providerId, 'put', false);
    }

    /**
     * @return \yii\httpclient\Response
     */
    public function reSend()
    {
        return $this->sendRequest([], 'orders/' . $this->providerId . '/resend', 'post', false);
    }
}