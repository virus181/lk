<?php
namespace app\commands;

use app\models\Order;
use yii\console\Controller;

class TestController extends Controller
{
    public function actionClearPhones()
    {
        $orders = Order::find()->all();

        foreach ($orders as $order) {
            $order->updated_at = time();
            $order->validate();
            $order->save(false);
        }
    }
}
