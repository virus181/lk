<?php

namespace app\commands;

use app\components\DeliveryErrorFixer;
use app\components\DeliveryStatusUpdater;
use Yii;
use yii\console\Controller;

class DeliveryStatusUpdaterController extends Controller
{
    public function actionIndex()
    {
        /** @var DeliveryStatusUpdater $deliveryStatusUpdater */
        $deliveryStatusUpdater = Yii::$app->get('deliveryStatusUpdater');
        $deliveryStatusUpdater->updateInSendingOrders();
    }

    public function actionDispatchNumberUpdater()
    {
        /** @var DeliveryStatusUpdater $dispatchNumberUpdater */
        $dispatchNumberUpdater = Yii::$app->get('deliveryStatusUpdater');
        $dispatchNumberUpdater->updateDispatchNumbers();
    }

    public function actionCancelErrorOrders()
    {
        /** @var DeliveryStatusUpdater $dispatchNumberUpdater */
        $dispatchNumberUpdater = Yii::$app->get('deliveryStatusUpdater');
        $dispatchNumberUpdater->cancelErrorOrders();
    }
}