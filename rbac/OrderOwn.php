<?php

namespace app\rbac;

use app\models\Order;
use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

class OrderOwn extends Rule
{
    public $name = '/order/own';

    public function execute($user, $item, $params)
    {
        /** @var Order $order */
        $order = ArrayHelper::getValue($params, 'model', null);
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if ($order !== null) {
            return in_array($order->shop_id, $user->getAllowedShopIds());
        }

        return true;
    }
}