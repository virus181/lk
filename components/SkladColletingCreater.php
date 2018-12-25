<?php

namespace app\components;

use app\models\Order;
use app\models\Product;
use app\models\Shop;
use app\models\sklad\Catalog;
use app\models\sklad\Order as skladOrder;
use app\models\sklad\OrderCatalog;
use app\models\sklad\OrderStatus;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\db\Exception;

class SkladColletingCreater extends Component
{
    /** @var Order */
    public $order;

    public function __construct(Order $order, array $config = [])
    {
        $this->order = $order;
        parent::__construct($config);
    }

    public function createCollection()
    {
        $order = $this->order;
        /** @var Shop $shop */
        $shop = $order->shop;

        if ($shop->additional_id === null) {
            return true;
        }

        foreach ($order->orderProducts as $orderProduct) {
            if ($orderProduct->product->additional_id === null) {
                return true;
            }
        }

        /** @var Connection $db */
        $db = Yii::$app->get('db_sklad');
        $orderProducts = [];

        $transaction = $db->beginTransaction();

        try {
            if (skladOrder::findOne(['Name' => $order->shop_order_number, 'SNMarket' => $shop->additional_id])) {
                if (Yii::$app->get('session', false)) {
                    Yii::info([
                        'id' => $order->id,
                        'message' => 'Заказ в складской программе уже был создан'
                    ], 'sklad-error');
                    Yii::$app->session->addFlash('seccess', Yii::t('app', 'Заказ в складской программе уже был создан'));
                }
                return true;
            }

            $skladOrder = new skladOrder();
            $skladOrder->Name = $order->shop_order_number;
            $skladOrder->SNMarket = $shop->additional_id;
            $skladOrder->SNShipping = $skladOrder->getShippingId($order);
            if ($skladOrder->save() === false) {
                Yii::info([
                    'id' => $order->id,
                    'message' => "Sklad Order save error: \n" . print_r($skladOrder->errors, true)
                ], 'sklad-error');
                Yii::error("Sklad Order save error: \n" . print_r($skladOrder->errors, true), __METHOD__);
                $order->addErrors($skladOrder->errors);
                $order->addError('main', Yii::t('app', 'Collenting saving error'));
                $transaction->rollBack();
                return false;
            }

            $skladOrderStatus = new OrderStatus();
            $skladOrderStatus->SNOrder = $skladOrder->SN;
            if ($skladOrderStatus->save() === false) {
                Yii::info([
                    'id' => $order->id,
                    'message' => "Sklad OrderStatus save error: \n" . print_r($skladOrderStatus->errors, true)
                ], 'sklad-error');
                Yii::error("Sklad OrderStatus save error: \n" . print_r($skladOrderStatus->errors, true), __METHOD__);
                $order->addErrors($skladOrderStatus->errors);
                $order->addError('main', Yii::t('app', 'Collenting saving error. Status can not be applied to order'));
            }

            foreach ($order->orderProducts as $orderProduct) {

                if (!Catalog::find()
                    ->andWhere(['SN' => $orderProduct->product->additional_id])
                    ->andWhere(['>=', 'Exist', $orderProduct->quantity])
                    ->one()
                ) {
                    Yii::info([
                        'id' => $order->id,
                        'message' => Yii::t('app', 'Product {name} missing in the required quantity', ['name' => $orderProduct->product->name])
                    ], 'sklad-error');
                    $order->addError('main', Yii::t('app', 'Product {name} missing in the required quantity', ['name' => $orderProduct->product->name]));
                }

                $orderCatalog = new OrderCatalog();
                $orderCatalog->SNOrder = $skladOrder->SN;
                $orderCatalog->SNCatalog = $orderProduct->product->additional_id;
                $orderCatalog->Count = $orderProduct->quantity;
//                Catalog::updateAllCounters(
//                    [
//                        'Exist' => $orderProduct->quantity * -1,
//                        'Ordered' => $orderProduct->quantity
//                    ],
//                    [
//                        'AND',
//                        ['SN' => $orderProduct->product->additional_id],
//                        ['>', 'Exist', 0]
//                    ]);
                $orderProducts[] = $orderCatalog;

                // Обновление остатков в ЛК
                $product = Product::find()->where(['id' => $orderProduct->product_id])->one();
                $product->count = $product->count - $orderProduct->quantity;
                $product->save();
            }

            /** @var OrderCatalog $orderProduct */
            foreach ($orderProducts as $orderProduct) {
                if ($orderProduct->validate() && $orderProduct->save()) {
                    $s = 1;
                } else {
                    Yii::error("Sklad OrderCatalog save error: \n" . print_r($orderProduct->errors, true), __METHOD__);
                    Yii::info([
                        'id' => $order->id,
                        'message' => $orderProduct->errors,
                        'text' => 'Collenting saving error. Product can not be applied to order'
                    ], 'sklad-error');
                    $order->addErrors($orderProduct->errors);
                    $order->addError('main', Yii::t('app', 'Collenting saving error. Product can not be applied to order'));
                    break;
                }
            }

            if ($order->hasErrors()) {
                $order->addError('main', Yii::t('app', 'Collenting saving error'));
                Yii::info([
                    'id' => $order->id,
                    'message' => Yii::t('app', 'Collenting saving error')
                ], 'sklad-error');
                $transaction->rollBack();
                return false;
            }

            $totalCount = 0;
            $products = OrderCatalog::find()->where(['SNOrder' => $skladOrder->SN])->asArray()->all();
            foreach ($products as $product) {
                $totalCount += $product['Count'];
            }
            if ($totalCount != $order->getProductsCount()) {
                $order->addError('main', Yii::t('app', 'Product counts is not equal'));
                Yii::info([
                    'id' => $order->id,
                    'message' => Yii::t('app', 'Product counts is not equal')
                ], 'sklad-error');
                $transaction->rollBack();
                return false;
            }


            $transaction->commit();
            return true;
        } catch (Exception $e) {
            Yii::info([
                'id' => $order->id,
                'message' => Yii::t('app', 'Collenting saving error')
            ], 'sklad-error');
            $order->addError('main', Yii::t('app', 'Collenting saving error'));
            $order->addError('main', print_r($e, true));
            $transaction->rollBack();
            return false;
        }
    }
}