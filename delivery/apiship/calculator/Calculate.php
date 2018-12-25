<?php
namespace app\delivery\apiship\calculator;

use app\delivery\apiship\BaseApiShip;
use app\delivery\DeliveryHelper;
use app\models\DeliveryService;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\User;
use Yii;

class Calculate extends BaseApiShip
{
    /** @var Address */
    public $from;
    /** @var Address */
    public $to;
    /** @var int */
    public $timeout = 5000;
    /** @var int */
    public $weight;
    /** @var int */
    public $width = 1;
    /** @var int */
    public $height = 1;
    /** @var int */
    public $length = 1;
    /** @var float|int */
    public $assessedCost;
    /** @var float|int */
    public $codCost;
    /** @var string */
    public $pickupDate;
    /** @var bool */
    public $includeFees = true;
    /** @var array */
    public $pickupTypes = [];
    /** @var array */
    public $deliveryTypes = [];
    /** @var array */
    public $providerKeys = [];
    /** @var array */
    public $cacheParams = [];

    /**
     * @param \app\models\Common\Calculator $calculator
     */
    public function prepare(\app\models\Common\Calculator $calculator)
    {
        $this->from = new Address();
        $calculator->getFromAddress()->getCity() && $this->from->city = $calculator->getFromAddress()->getCity();
        $calculator->getFromAddress()->getCityFiasId() && $this->from->cityGuid = $calculator->getFromAddress()->getCityFiasId();
        $calculator->getFromAddress()->getAddressString() && $this->from->addressString = $calculator->getFromAddress()->getAddressString();

        $this->to = new Address();
        if ($calculator->getToAddress()) {
            $calculator->getToAddress()->getCity() && $this->to->city = $calculator->getToAddress()->getCity();
            $calculator->getToAddress()->getCityFiasId() && $this->to->cityGuid = $calculator->getToAddress()->getCityFiasId();
            $calculator->getToAddress()->getAddressString() && $this->to->addressString = $calculator->getToAddress()->getAddressString();
        }

        $this->weight = $calculator->weight;
        if ($calculator->width || $calculator->length || $calculator->height) {
            $this->width = (int) $calculator->width;
            $this->length = (int) $calculator->length;
            $this->height = (int) $calculator->height;
        }
        $this->assessedCost = $calculator->accessed_cost;
        $this->codCost = $calculator->cost;

        // Такой некрасивый костыль только потому что IML не обрабатывает заказы более чем с 9 вложениями
        $this->providerKeys = $calculator->getAllowedDeliveries();
        $key = array_search(DeliveryHelper::CARRIER_CODE_IML, $this->providerKeys);
        if ($calculator->product_count > 9 && $key !== false) {
            unset($this->providerKeys[$key]);
        }

        // Обновим список СД если включена частичка
        if ($calculator->partial) {
            $this->providerKeys = (new OrderDelivery())->getCarriersByService(
                $this->providerKeys,
                DeliveryService::SERVICE_PARTIAL
            );
        }

        $this->cacheParams = [
            'weight' => $this->weight,
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
            'cityFrom' => $this->from->cityGuid ? $this->from->cityGuid : $this->from->city,
            'cityTo' => $this->to->cityGuid ? $this->to->cityGuid : $this->to->city,
            'codCost' => $this->codCost,
            'assessedCost' => $this->assessedCost,
            'providerKeys' => $this->providerKeys,
        ];
    }

    /**
     * Получение готового Request
     *
     * @return \yii\httpclient\Request
     */
    public function getRequest()
    {
        return $this->prepareRequest(
            $this->getArr($this),
            'calculator',
            'post'
        );
    }
}