<?php

namespace app\delivery\apiship\orders;

class OrderData
{
    /** @var string */
    public $providerNumber;
    /** @var string */
    public $clientNumber = '';
    /** @var string */
    public $description = '';
    /** @var integer */
    public $height = 1;
    /** @var integer */
    public $length = 1;
    /** @var integer */
    public $width = 1;
    /** @var integer */
    public $weight;
    /** @var string */
    public $providerKey;
    /** @var integer */
    public $pickupType;
    /** @var integer */
    public $deliveryType;
    /** @var integer */
    public $tariffId;
    /** @var string */
    public $pickupDate;
    /** @var string */
    public $deliveryDate;
    /** @var integer */
    public $pointInId;
    /** @var integer */
    public $pointOutId;
    /** @var string */
    public $pickupTimeStart;
    /** @var string */
    public $pickupTimeEnd;
    /** @var string */
    public $deliveryTimeStart;
    /** @var string */
    public $deliveryTimeEnd;
}