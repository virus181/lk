<?php

namespace app\rbac;

use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

class UserOwn extends Rule
{
    public $name = '/user/own';

    public function execute($user, $item, $params)
    {
        /** @var User $model */
        $model = ArrayHelper::getValue($params, 'model', null);
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $userAllowed = $user->getAllowedShopIds();

        if ($model !== null && $userAllowed !== []) {
            if ($model->id === $user->id) {
                return true;
            }
            $modelAllowed = (array)$model->getAllowedShopIds();
            if (is_array($userAllowed)) {
                foreach ($userAllowed as $shopId) {
                    if (in_array($shopId, $modelAllowed)) {
                        return true;
                    }
                }
            }
            return false;
        }

        return true;
    }
}