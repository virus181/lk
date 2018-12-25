<?php

namespace app\api\controllers;

use app\api\base\BaseActiveController;
use app\delivery\Deliveries;
use app\models\Address;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Product;
use app\models\User;
use app\models\Warehouse;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\Request;

class HelperController extends BaseActiveController
{
    public $modelClass = 'app\models\Order';

    public function checkAccess($action, $model = null, $params = [])
    {
        return true;
    }

    /**
     * @return array
     */
    public function actionGetOrdersUrl()
    {
        $request = Yii::$app->request->get();
        unset($request['access-token']);

        // Если тип запроса info нужно вернуть URL карточки заказа
        if (empty($request['type'])) {
            $request['type'] = 'list';
        }
        if ($request['type'] == 'info') {
            $query = Order::find();
            foreach ($request as $key => $value) {
                if ($key == 'type') continue;
                if ($key == 'id') {
                    $query->andWhere([$key => $value]);
                } else {
                    $query->andWhere(['like', $key, $value]);
                }
            }
            $orders = $query->all();
            $list = [];
            foreach ($orders as $order) {
                $list[]['url'] = Url::to(['/order/view', 'id' => $order->id], true);
            }
            return $list;
        }
        return [['url' => Url::to('/orders?' . http_build_query($request), true)]];
    }
}