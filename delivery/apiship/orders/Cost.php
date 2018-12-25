<?php

namespace app\delivery\apiship\orders;

class Cost
{
    /** @var float */
    public $assessedCost;
    /** @var float */
    public $deliveryCost;
    /** @var integer */
    public $deliveryCostVat;
    /** @var float */
    public $codCost;
    /** @var boolean */
    public $isDeliveryPayedByRecipient = false;
}