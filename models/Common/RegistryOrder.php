<?php
namespace app\models\Common;

use yii\base\Model;

class RegistryOrder extends Model
{
    /** @var \app\models\Repository\RegistryOrder */
    private $order;

    /**
     * @param \app\models\Repository\RegistryOrder $order
     * @param array $config
     */
    public function __construct(\app\models\Repository\RegistryOrder $order, array $config = [])
    {
        $this->order = $order;
        parent::__construct($config);
    }

    /**
     * Реальная стоимость доставки
     *
     * @return float
     */
    public function getActualDeliveryCost(): float
    {
        $cost = (float) $this->order->agency_charge_fastery
            + (float) $this->order->agency_charge
            + (float) $this->order->delivery_cost
            + (float) $this->order->fastery_charge;
        return $cost;
    }
}