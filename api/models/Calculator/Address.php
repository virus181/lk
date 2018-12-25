<?php
namespace app\api\models\Calculator;

use yii\base\Model;

class Address extends Model
{
    /** @var string */
    private $city;
    /** @var string */
    private $cityFiasId;
    /** @var string */
    private $addressString;

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Address
     */
    public function setCity(string $city): Address
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCityFiasId(): ?string
    {
        return $this->cityFiasId;
    }

    /**
     * @param string $cityFiasId
     * @return Address
     */
    public function setCityFiasId(string $cityFiasId): Address
    {
        $this->cityFiasId = $cityFiasId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressString(): ?string
    {
        return $this->addressString;
    }

    /**
     * @param string $addressString
     * @return Address
     */
    public function setAddressString(string $addressString): Address
    {
        $this->addressString = $addressString;
        return $this;
    }
}