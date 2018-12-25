<?php
namespace app\delivery;

use app\models\Common\Calculator;
use app\models\Helper;
use app\models\OrderDelivery;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\httpclient\Response;

class Delivery extends Component
{
    /** @var array */
    public $providers = [
        'app\delivery\apiship\Delivery',
        'app\delivery\own\Delivery',
    ];

    /** @var Calculator*/
    public $calculator;
    /** @var OrderDelivery */
    public $orderDeliveryModel;

    /**
     * Deliveries constructor.
     * @param Calculator $calculator
     * @param OrderDelivery $orderDeliveryModel
     * @param array $config
     */
    public function __construct($calculator, $orderDeliveryModel, $config = [])
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

        $this->calculator = $calculator;
        $this->orderDeliveryModel = $orderDeliveryModel;

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
            $provider->setCalculator($this->calculator);
            if($request = $provider->getDeliveryRequest()) {
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
                $provider->getCalculatorDeliveries(
                    isset($responses[$class]) ? $responses[$class] : new Response(),
                    $this->orderDeliveryModel)
            );
        }

        return $orderDeliveries;
    }
}