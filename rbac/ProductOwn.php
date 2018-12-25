<?php

namespace app\rbac;

use app\models\Product;
use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

class ProductOwn extends Rule
{
    public $name = '/product/own';

    public function execute($user, $item, $params)
    {
        /** @var Product $product */
        $product = ArrayHelper::getValue($params, 'model', null);
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if ($product !== null) {
            return in_array($product->shop_id, $user->getAllowedShopIds());
        }

        return true;
    }
}