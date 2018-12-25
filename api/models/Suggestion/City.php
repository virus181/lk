<?php
namespace app\api\models\Suggestion;

use Yii;
use yii\base\Model;

class City
{
    /** @var string */
    public $country;

    /** @var string */
    public $regionWithType;

    /** @var string */
    public $regionFiasId;

    /** @var string */
    public $city;

    /** @var string */
    public $cityType;

    /** @var string */
    public $cityTypeFull;

    /** @var string */
    public $cityWithType;

    /** @var string */
    public $cityFiasId;
}