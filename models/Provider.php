<?php

namespace app\models;

use app\delivery\DeliveryHelper;

class Provider
{
    CONST PROVIDERS = [
        'cdek',
        'dpd',
        'boxberry',
        'b2cpl',
        'maxi',
        'iml',
        'pickpoint',
        'own',
        'dalli',
        'viehali',
        'on-time',
        'easyway'
    ];

    public static function getProviders()
    {
        $providers = [];
        foreach (self::PROVIDERS as $provider) {
            $providers[$provider] = DeliveryHelper::getName($provider);
        }
        return $providers;
    }
}
