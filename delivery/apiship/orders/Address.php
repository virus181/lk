<?php

namespace app\delivery\apiship\orders;

class Address
{
    /** @var string */
    public $addressString;
    /** @var float */
    public $lat;
    /** @var float */
    public $lng;
    /** @var integer */
    public $postIndex;
    /** @var string */
    public $countryCode = 'RU';
    /** @var string */
    public $region = 'Московская область';
    /** @var string */
    public $area;
    /** @var string */
    public $city = 'Москва';
    /** @var string */
    public $cityGuid;
    /** @var string */
    public $street = 'Викторенко';
    /** @var integer */
    public $house = '12';
    /** @var string */
    public $block;
    /** @var integer */
    public $office;
    /** @var string */
    public $companyName;
    /** @var string */
    public $contactName = 'Иванов Иван Иванович';
    /** @var string */
    public $phone = '+79999999999';
    /** @var string */
    public $email;
    /** @var string */
    public $comment;
}