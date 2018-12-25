<?php
namespace app\models\Helper;

use app\models\Repository\Invoice;
use Yii;

class Document
{

    public function getTypeList(): array
    {
        return $statuses = [
            Invoice::TYPE_INVOICE => Yii::t('shop', 'Invoice'),
            Invoice::TYPE_CHARGE => Yii::t('shop', 'Charge'),
        ];
    }
}