<?php

namespace app\commands;

use app\components\DeliveryErrorFixer;
use Yii;
use yii\console\Controller;

class FixerDeliveryErrorStatusController extends Controller
{
    public function actionIndex($id = null)
    {
        /** @var DeliveryErrorFixer $deliveryErrorFixer */
        $deliveryErrorFixer = Yii::$app->get('deliveryErrorFixer');
        $deliveryErrorFixer->fixAllOrders($id);
    }
}