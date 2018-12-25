<?php
namespace app\delivery;

use app\models\Delivery;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class DeliveryHelper
{
    const CARRIER_CODE_B2CPL = 'b2cpl';
    const CARRIER_CODE_CDEK = 'cdek';
    const CARRIER_CODE_DPD = 'dpd';
    const CARRIER_CODE_BOXBERRY = 'boxberry';
    const CARRIER_CODE_DALLI = 'dalli';
    const CARRIER_CODE_MAXI = 'maxipost';
    const CARRIER_CODE_PICKPOINT = 'pickpoint';
    const CARRIER_CODE_IML = 'iml';
    const CARRIER_CODE_DOSTAVISTA = 'dostavista';
    const CARRIER_CODE_OWN = 'own';
    const CARRIER_CODE_VIEHALI = 'viehali';
    const CARRIER_CODE_ONTIME = 'on-time';
    const CARRIER_CODE_EASYWAY = 'easyway';

    const SERVICE_KEY_PARTIAL = 'partial';

    const PICKPOINT_TIME_START_INTERVALS = [
        9 => 14,
        14 => 18
    ];

    public static $deliveryCodeAliases = [
        'sdek' => 'cdek',
        'cdek' => 'cdek',
        'b2cpl' => 'b2cpl',
        'b2spl' => 'b2cpl',
        'b2c' => 'b2cpl',
        'b2s' => 'b2cpl',
        'dpd' => 'dpd',
        'pony' => 'pony',
        'pony-express' => 'pony',
        'pony_express' => 'pony',
        'shoplogistics' => 'shoplogistics',
        'shop-logistics' => 'shoplogistics',
        'shoplogist' => 'shoplogistics',
        'boxberry' => 'boxberry',
        'pickpoint' => 'pickpoint',
        'spsr' => 'spsr',
        'pochta' => 'pochta',
        'iml' => 'iml',
        'maxipost' => 'maxipost',
        'maxi-post' => 'maxipost',
        'maxi' => 'maxipost',
        'dalli' => 'dalli',
        'dostavista' => 'dostavista',
        'dаstavista' => 'dostavista',
        'dalli-service' => 'dalli',
        'own' => 'own',
        'viehali' => 'viehali',
        'on-time' => 'on-time',
        'ontime' => 'on-time',
        'onetime' => 'on-time',
        'easyway' => 'easyway',
        'easeway' => 'easyway',
        'esyway' => 'easyway',
    ];

    public static $mailTariffMapper = [
        156 => 162,
        157 => 164,
        158 => 163,
        159 => 165,
        160 => 0,
        161 => 0,
        162 => 156,
        163 => 158,
        164 => 157,
        165 => 159,
        166 => 167,
        167 => 166,
        168 => 0,
    ];

    public static $deliveryNames = [
        'cdek' => 'CDEK',
        'dpd' => 'DPD',
        'b2cpl' => 'B2CPL',
        'pony' => 'Pony Express',
        'shoplogistics' => 'Shop-Logistics',
        'boxberry' => 'BoxBerry',
        'pickpoint' => 'PickPoint',
        'spsr' => 'SPSR',
        'pochta' => 'Почта РФ',
        'iml' => 'IML',
        'maxipost' => 'MaxiPost',
        'dalli' => 'Dalli Service',
        'dostavista' => 'Dostavista',
        'own' => 'Собственная СД',
        'viehali' => 'Уже выехали',
        'on-time' => 'On-time',
        'easyway' => 'ПЭК Easyway',
    ];

    public static $deliveryWeightLimits = [
        'cdek' => 30000,
        'dpd' => 30000,
        'b2cpl' => 60000,
        'pony' => 30000,
        'shoplogistics' => 30000,
        'boxberry' => 15000,
        'pickpoint' => 30000,
        'spsr' => 30000,
        'pochta' => 30000,
        'iml' => 30000,
        'maxi' => 30000,
        'dalli' => 30000,
        'dostavista' => 30000,
        'own' => 30000,
        'viehali' => 30000,
        'on-time' => 30000,
        'easyway' => 3000000,
    ];

    public static $сdekTariffs = [
        11, 136, 137
    ];

    public static $now;

    public static function getIconPath($deliveryCode)
    {
        return Url::to('@web/delivery/' . self::getNormalCode($deliveryCode) . '.png');
    }

    public static function getIconThumbPath($deliveryCode)
    {
        return \Yii::getAlias('@webroot') . Url::to('/delivery/' . self::getNormalCode($deliveryCode) . '-thumb.jpg');
    }

    public static function getNormalCode($deliveryCode)
    {
        return ArrayHelper::getValue(self::$deliveryCodeAliases, strtolower($deliveryCode), 'udefined');
    }

    public static function getName($deliveryCode)
    {
        return ArrayHelper::getValue(self::$deliveryNames, self::getNormalCode($deliveryCode), $deliveryCode);
    }

    public static function getDescription($deliveryCode)
    {
        return ArrayHelper::getValue(Delivery::find()->where(['carrier_key' => $deliveryCode])->asArray()->one(), 'description');
    }

    public static function getDeliveryDate($startTerm, $minTerm, $maxTerm)
    {
        setlocale(LC_ALL, 'ru_RU.UTF-8');
        if (!is_numeric($startTerm)) {
            $startTerm = strtotime($startTerm);
        }

        if ($minTerm === $maxTerm) {
            return date('d.m.Y', strtotime(date('Y-m-d', $startTerm) . " +$minTerm day"));
        } else {
            return date('d.m', strtotime(date('Y-m-d', $startTerm) . " +$minTerm day")) . ' - ' . date('d.m.Y', strtotime(date('Y-m-d', $startTerm) . " +$maxTerm day"));
        }
    }

    public static function getPickupDate($pickupDate)
    {
        if (!is_integer($pickupDate)) {
            $pickupDate = strtotime($pickupDate);
        }

        setlocale(LC_ALL, 'ru_RU.UTF-8');
        return date('d.m.Y', $pickupDate);
    }
}