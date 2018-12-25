<?php
namespace app\models\Helper;

use Yii;

class Status
{
    const STATUS_ACTIVE = 1;
    const STATUS_UN_ACTIVE = 0;

    public function getStatusList(): array
    {
        return $statuses = [
            self::STATUS_ACTIVE => Yii::t('shop', 'Paid'),
            self::STATUS_UN_ACTIVE => Yii::t('shop', 'Unpaid'),
        ];
    }
}