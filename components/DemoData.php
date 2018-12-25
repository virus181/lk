<?php
namespace app\components;

use app\models\Address;
use app\models\Courier;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Product;
use app\models\Shop;
use app\models\User;
use app\models\Warehouse;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class DemoData extends Component
{
    /**
     * Инициализация компонента
     */
    public function init()
    {
        if (!(ArrayHelper::getValue(Yii::$app->params, 'demo.autofill')
            && ArrayHelper::getValue(Yii::$app->params, 'demo'))) {
            throw new InvalidParamException('Can not delete production data');
        }
        parent::init();
    }

    /**
     * Удалим демо данные которые были созданы более 30 дней назад
     */
    public function clearDemoData()
    {
        $deletedTime = time() - (86400 * 30);

        Yii::$app->db->createCommand("SET foreign_key_checks = 0")->execute();

        Product::deleteAll(['<', 'created_at', $deletedTime]);
        OrderDelivery::deleteAll(['<', 'created_at', $deletedTime]);
        Order::deleteAll(['<', 'created_at', $deletedTime]);
        Address::deleteAll(['<', 'created_at', $deletedTime]);
        User::deleteAll('created_at < '.$deletedTime.' AND id NOT IN (251, 149)');
        Shop::deleteAll(['<', 'created_at', $deletedTime]);
        Warehouse::deleteAll(['<', 'created_at', $deletedTime]);
        Courier::deleteAll(['<', 'created_at', $deletedTime]);

        Yii::$app->db->createCommand("SET foreign_key_checks = 1")->execute();
    }
}