<?php
namespace app\delivery;

use app\models\Courier;
use app\models\Helper;
use app\models\Order;
use app\models\OrderDelivery;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Response;

class Deliveries extends Component
{
    /**
     * @var array
     */
    public $providers = [
        'app\delivery\apiship\Delivery',
        'app\delivery\own\Delivery',
    ];

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Courier
     */
    public $courier;

    /**
     * @var OrderDelivery
     */
    public $orderDeliveryModel;

    /**
     * Deliveries constructor.
     * @param Order|Order[] $order
     * @param Courier $courier
     * @param OrderDelivery $orderDeliveryModel
     * @param array $config
     * @internal param string $scenario
     */
    public function __construct($order, $orderDeliveryModel, $courier = null, $config = [])
    {
        if ($orderDeliveryModel !== null && $orderDeliveryModel instanceof OrderDelivery === false) {
            throw new InvalidParamException('$orderDeliveryModel must be set');
        }

        foreach ($this->providers as $index => $providerClassName) {
            /** @var DeliveryInterface $provider */
            $provider = Yii::createObject($providerClassName);
            if ($provider instanceof DeliveryInterface) {
                $this->providers[$providerClassName] = $provider;
                unset($this->providers[$index]);
            }
        }

        $this->order = $order;
        $this->orderDeliveryModel = $orderDeliveryModel;
        $this->courier = $courier;

        parent::__construct($config);
    }

    /**
     * @return OrderDelivery[]
     */
    public function calculate()
    {
        $orderDeliveries = [];
        $requests = [];
        $cacheParams = [];

        /** @var DeliveryInterface $provider */
        foreach ($this->providers as $class => $provider) {
            $provider->setOrder($this->order);
            if($request = $provider->getOrderDeliveryRequest($this->orderDeliveryModel)) {
                $requests[$class] = $request;
                $cacheParams[] = $request->client->cacheParams;
            }
        }

        $responses = Yii::$app->cache->getOrSet($cacheParams, function () use ($requests) {
            return (new BaseClient())->batchSend($requests);
        }, Helper::MIN_CACHE_VALUE);

        foreach ($this->providers as $class => $provider) {
            $orderDeliveries = array_merge(
                $orderDeliveries,
                $provider->getDeliveries(
                    isset($responses[$class]) ? $responses[$class] : new Response(),
                    $this->orderDeliveryModel)
            );
        }

        return $orderDeliveries;
    }

    /**
     * @return bool|Response
     */
    public function createOrder()
    {
        /** @var DeliveryInterface $provider */
        if ($provider = ArrayHelper::getValue($this->providers, $this->order->delivery->class_name_provider)) {
            $provider->setOrder($this->order);
            return $provider->createOrder();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function updateOrder()
    {
        /** @var DeliveryInterface $provider */
        if ($provider = ArrayHelper::getValue($this->providers, $this->order->delivery->class_name_provider)) {
            $provider->setOrder($this->order);
            return $provider->updateOrder();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function reSendOrder()
    {
        /** @var DeliveryInterface $provider */
        if ($provider = ArrayHelper::getValue($this->providers, $this->order->delivery->class_name_provider)) {
            $provider->setOrder($this->order);
            return $provider->reSendOrder();
        }

        return false;
    }

    public function callCourier()
    {
        /** @var DeliveryInterface $provider */
        if ($provider = ArrayHelper::getValue($this->providers, $this->courier->class_name_provider)) {
            $provider->setCourier($this->courier);
            return $provider->call();
        }

        return false;
    }

    public function getRegistries()
    {
        /** @var DeliveryInterface $provider */
        if ($provider = ArrayHelper::getValue($this->providers, $this->courier->class_name_provider)) {
            $provider->setCourier($this->courier);
            return $provider->getRegistries();
        }

        return false;
    }

    public function getLabels()
    {
        $orders = [];
        /** @var Order $order */
        foreach ($this->order as $order) {
            $orders[$order->delivery->class_name_provider][$order->provider_number] = $order;
        }

        foreach ($orders as $providerClassName => $orderIds) {
            /** @var DeliveryInterface $provider */
            if ($provider = ArrayHelper::getValue($this->providers, $providerClassName)) {
                return $provider->getLabels($orderIds);
            }
        }

        return null;
    }

    public function getLastStatuses()
    {
        $orders = [];
        /** @var Order $order */
        foreach ($this->order as $order) {

            if (isset($order->provider_number) && isset($order->delivery)) {
                $orders[$order->delivery->class_name_provider][$order->provider_number] = $order;
            } else {
                echo $order->id."\n";
            }

        }

        foreach ($orders as $providerClassName => $orderIds) {
            /** @var DeliveryInterface $provider */
            if ($provider = ArrayHelper::getValue($this->providers, $providerClassName)) {
                return $provider->getLastStatuses($orderIds);
            }
        }

        return null;
    }

    public function getStatusHistory()
    {
        /** @var DeliveryInterface $provider */
        if (isset($this->order->delivery->class_name_provider) && $provider = ArrayHelper::getValue($this->providers, $this->order->delivery->class_name_provider)) {
            return $provider->getStatusesHistory($this->order->provider_number);
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentStatus()
    {
        /** @var DeliveryInterface $provider */
        if (isset($this->order->delivery->class_name_provider) && $provider = ArrayHelper::getValue($this->providers, $this->order->delivery->class_name_provider)) {
            return $provider->getCurrentStatus($this->order->getOrderNumber());
        }

        return null;
    }

    /**
     * @param $providerHash string
     * @return bool|string
     */
    public function getClassNameByProviderHash($providerHash)
    {
        $providersClassName = array_keys($this->providers);

        foreach ($providersClassName as $className) {
            if (substr(md5($className), -6) === $providerHash) {
                return $className;
            }
        }

        return false;
    }
}