<?php
namespace app\delivery\apiship;

class Courier extends BaseApiShip
{
    /** @var string */
    public $url = 'courierCall';
    /** @var string */
    public $providerKey;
    /** @var string */
    public $date;
    /** @var string */
    public $timeStart = '10:00';
    /** @var string */
    public $timeEnd = '18:00';
    /** @var integer */
    public $weight = 0;
    /** @var integer */
    public $width = 10;
    /** @var integer */
    public $height = 10;
    /** @var integer */
    public $length = 10;
    /** @var array */
    public $orderIds = [];
    /** @var integer */
    public $postIndex;
    /** @var string */
    public $countryCode;
    /** @var string */
    public $region;
    /** @var string */
    public $area;
    /** @var string */
    public $city;
    /** @var string */
    public $cityGuid;
    /** @var string */
    public $street;
    /** @var string */
    public $house;
    /** @var string */
    public $block;
    /** @var string */
    public $office;
    /** @var string */
    public $companyName;
    /** @var string */
    public $contactName;
    /** @var string */
    public $phone;
    /** @var string */
    public $email;

    /**
     * @param \app\models\Courier $courier
     * @param array $config
     */
    public function __construct($courier, $config = [])
    {
        $this->providerKey = $courier->carrier_key;
        $this->date = date('Y-m-d', $courier->pickup_date);
        $this->timeStart = $courier->pickup_time_start;
        $this->timeEnd = $courier->pickup_time_end;

        foreach ($courier->orders as $order) {
            $this->weight += $order->weight;
            $this->orderIds[] = $order->provider_number;
        }

        $this->region = $courier->warehouse->address->region;
        $this->city = $courier->warehouse->address->city;
        $this->cityGuid = $courier->warehouse->address->city_fias_id;
        $this->street = $courier->warehouse->address->street;
        $this->house = $courier->warehouse->address->house;
        if($courier->warehouse->address->postcode) {
            $this->postIndex = $courier->warehouse->address->postcode;
        }
        $this->block = $courier->warehouse->address->housing;
        $this->office = $courier->warehouse->address->flat ? $courier->warehouse->address->flat : '---';
        $this->contactName = $courier->warehouse->contact_fio;
        $this->phone = $courier->warehouse->getClearPhone();

        parent::__construct($config);
    }

    public function call()
    {
        return $this->sendRequest((array) $this, $this->url, 'post', false);
    }
}