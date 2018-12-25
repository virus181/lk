<?php
namespace app\components;

use app\components\Clients;
use app\models\Order;
use app\models\Shop;
use yii\base\Component;

class Stat extends Component
{
    /**
     * Запись статстики
     *
     * @param int $date
     * @internal param int $time
     */
    public function setOrderStat(int $date = 0)
    {
        $client = new Clients\Analitics();
        $time   = $date ? $date : 1504224000;
        while ($time < time()) {
            $countOrders = Order::find()
                ->where(['>', 'created_at', $time])
                ->andWhere(['<', 'created_at', $time + 86400])
                ->andWhere(['NOT IN', 'shop_id', Shop::TEST_IDS])
                ->count();
            $time        += 86400;
            $client->sendRequest(['courier' => 'all'], $countOrders, 'orders', $time * 1000000000, 'POST');
        }
    }


    /**
     * Запись статстики магазинов
     */
    public function setShopsStat()
    {
        $client = new Clients\Analitics();

        $countAllShops         = Shop::find()->count();
        $countActiveShops      = Shop::find()->where(['status' => 10])->count();
        $countFulfillmetnShops = Shop::find()->where(['status' => 10, 'fulfillment' => 1])->count();

        $client->sendRequest(['type' => 'all'], $countAllShops, 'shops', (time() - 86400) * 1000000000, 'POST');
        $client->sendRequest(['type' => 'active'], $countActiveShops, 'shops', (time() - 86400) * 1000000000, 'POST');
        $client->sendRequest(['type' => 'fulfillment'], $countFulfillmetnShops, 'shops', (time() - 86400) * 1000000000, 'POST');
    }

    /**
     * Запись статстики магазинов
     */
    public function setTotalOrderStat()
    {
        $client = new Clients\Analitics();

        $time      = time();
        $countOrders         = Order::find()
            ->andWhere(['<', 'created_at', $time])
            ->andWhere(['NOT IN', 'shop_id', Shop::TEST_IDS])
            ->andWhere(['!=', 'status', 'OrderWorkflow/canceled'])
            ->count();
        $countDeliveryOrders = Order::find()
            ->andWhere(['<', 'created_at', $time])
            ->andWhere(['NOT IN', 'shop_id', Shop::TEST_IDS])
            ->andWhere(['status' => 'OrderWorkflow/delivered'])
            ->count();
        $client->sendRequest(['type' => 'all'], $countOrders, 'totals', $time * 1000000000, 'POST');
        $client->sendRequest(['type' => 'delivered'], $countDeliveryOrders, 'totals', $time * 1000000000, 'POST');
    }

    /**
     * Отправить сообщение об ошибке
     *
     * @param array $params
     */
    public function sendErrorEvent(array $params = [])
    {
        $client = new Clients\Analitics();
        $time      = time();
        $client->sendRequest($params, 1, 'error', $time * 1000000000, 'POST');
    }
}