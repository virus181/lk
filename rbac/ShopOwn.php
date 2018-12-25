<?php
namespace app\rbac;

use app\models\Shop;
use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

class ShopOwn extends Rule
{
    public $name = '/shop/own';

    public function execute($user, $item, $params)
    {
        /** @var Shop $shop */
        $shop = ArrayHelper::getValue($params, 'model', null);
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if ($shop !== null) {
            return in_array($shop->id, $user->getAllowedShopIds());
        }

        return true;
    }
}