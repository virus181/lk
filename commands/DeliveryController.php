<?php
namespace app\commands;

use app\components\Delivery;
use app\models\Order;
use Yii;
use app\models\sklad\Catalog;
use app\models\sklad\Order as skladOrder;
use app\models\sklad\OrderCatalog;
use app\models\sklad\OrderStatus;
use yii\console\Controller;

class DeliveryController extends Controller
{
    public function actionFormBoxberryActs()
    {
        /** @var Delivery $delivery */
        $delivery = Yii::$app->get('delivery');
        $delivery->formBoxberryActs();
    }

    public function actionSklad()
    {
        $order = skladOrder::findOne(['Name' => 'Тест-001', 'SNMarket' => 3]);
        $products = OrderCatalog::find()->where(['SNOrder' => $order->SN])->asArray()->all();
        print_r($order);
        foreach ($products as $product) {
            print_r($product);

        }
        echo "======================";
        echo count($products);
    }
}
