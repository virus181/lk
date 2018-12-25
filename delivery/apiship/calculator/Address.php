<?php
namespace app\delivery\apiship\calculator;

use yii\base\Model;

class Address extends Model
{
    /** @var string */
    public $cityGuid;
    /** @var string */
    public $region;
    /** @var string */
    public $city = 'Москва';
    /** @var string */
    public $countryCode = 'RU';
    /** @var string */
    public $addressString;
}