<?php
namespace app\controllers;

use app\models\Address;
use app\models\Helper;
use app\models\OrderDelivery;
use app\models\Rate;
use app\models\RateInventory;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class RateController extends Controller
{
    /**
     * @param int $shopId
     * @return string
     */
    public function actionIndex($shopId)
    {
        $rates = Rate::find()->where(['shop_id' => $shopId])->all();
        return $this->renderAjax('_list', [
            'rates' => $rates
        ]);
    }

    /**
     * Добавление / Редактирование пользовательской СД
     *
     * @param $shopId
     * @param bool $rateId
     * @return string
     */
    public function actionAdd($shopId, $rateId = false)
    {
        if (!$rateId) {
            $rate = new Rate();
            $rate->shop_id = $shopId;
            $address = new Address();
            $address->scenario = Address::SCENARIO_ADDRESS_FULL;
            $rate->address = $address;
        } else {
            $rate = Rate::find()
                ->joinWith(['inventories'])
                ->where([
                    'rate.shop_id' => $shopId,
                    'rate.id' => $rateId
                ])->one();
        }

        $deliveryMethods = array_merge(
            [Helper::EMPTY_VALUE => Yii::t('app', 'All methods')],
            Helper::getDeliveryMethods()
        );

        if ($post = Yii::$app->request->post()) {
            if ($rateId) {
                RateInventory::deleteAll(['rate_id' => $rateId]);
            }

            $rate->address->load($post);
            $rate->load(Yii::$app->request->post());
            $rateValidate = $rate->validate();
            $rate->address->scenario = Address::SCENARIO_DEFAULT;
            if ($rate->type == OrderDelivery::DELIVERY_TO_POINT) {
                $rate->address->scenario = Address::SCENARIO_ADDRESS_FULL;
            }
            $addressValidate = $rate->address->validate();

            if ($addressValidate) {
                $rate->fias_to = !empty($rate->address->city_fias_id) ? $rate->address->city_fias_id : null;
            }

            $inventory = [];
            foreach (Yii::$app->request->post('RateInventory', []) as $i => $inventoryRequest) {
                $inventory[$i] = new RateInventory();
            }

            RateInventory::loadMultiple($inventory, $post);
            $validateInventory = RateInventory::validateMultiple($inventory);
            $rate->inventories = $inventory;
            if ($validateInventory) {
                foreach ($rate->inventories as $key => $inventory) {
                    $rate->inventories[$key]->weight_from = $inventory->weight_from * 1000;
                    $rate->inventories[$key]->weight_to = $inventory->weight_to * 1000;
                }
            }

            $transaction = Yii::$app->db->beginTransaction();

            if ($rateValidate && $addressValidate && $validateInventory && $rate->save()) {
                $id = $rate->id;
                foreach ($rate->inventories as $item) {
                    $item->rate_id = $id;
                    $item->save();
                }
                Yii::$app->session->addFlash('success', Yii::t('app', 'Courier has been created'));
                $transaction->commit();
                return $this->redirect(['shop/update', 'id' => $shopId]);
            } else {
                if ($post) {
                    if (!$rateValidate) {
                        Yii::$app->session->addFlash('danger', Yii::t('app', 'Rate errors'));
                    }
                    if (!$addressValidate) {
                        Yii::$app->session->addFlash('danger', Yii::t('app', 'Address errors'));
                    }
                    if (!$validateInventory) {
                        Yii::$app->session->addFlash('danger', Yii::t('app', 'Inventory errors'));
                    }
                    $transaction->rollBack();
                }

                return $this->renderAjax('_add', [
                    'rate' => $rate,
                    'deliveryMethods' => $deliveryMethods
                ]);
            }
        }

        if (!$rate->inventories) {
            $rate->inventories = [new RateInventory()];
        }

        return $this->renderAjax('_add', [
            'rate' => $rate,
            'deliveryMethods' => $deliveryMethods
        ]);
    }

    /**
     * @param $i
     * @return string
     */
    public function actionGetInventoryRow($i)
    {
        $inventory = new RateInventory();

        return $this->renderAjax('_inventory', [
            'inventory' => $inventory,
            'i' => ++$i,
        ]);
    }

    /**
     * @return array
     */
    public function actionDelete(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        if (!empty($post['rateId'])) {
            RateInventory::deleteAll(['rate_id' => $post['rateId']]);
            $deletedRows = Rate::deleteAll(['id' => $post['rateId']]);
            return [
                'success' => $deletedRows > 0
            ];
        }
        return [
            'success' => false
        ];
    }
}
