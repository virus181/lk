<?php

namespace app\controllers;

use app\models\Delivery;
use app\models\Helper;
use app\models\Tariff;
use Yii;
use app\components\UserException;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * ShopController implements the CRUD actions for Shop model.
 */
class TariffController extends Controller
{
    /**
     * Добавление / Редактирование пользовательского тарифа
     *
     * @param $shopId
     * @return string
     */
    public function actionAdd($shopId, $tariffId = false)
    {
        if (!$tariffId) {
            $tariff = new Tariff();
            $tariff->shop_id = $shopId;
        } else {
            $tariff = Tariff::find()->where([
                'shop_id' => $shopId,
                'id' => $tariffId
            ])->one();
        }

        $tariff->detailed = Tariff::isDetailed($tariff);

        $carrierKeys = array_merge(
            [Helper::EMPTY_VALUE => Yii::t('app', 'All deliveries')],
            ArrayHelper::map(
                Delivery::find()->where(['status' => Helper::STATUS_ACTIVE])->asArray()->all(),
                'carrier_key',
                'name'
            )
        );

        $deliveryMethods = array_merge(
            [Helper::EMPTY_VALUE => Yii::t('app', 'All methods')],
            Helper::getDeliveryMethods()
        );

        if (Yii::$app->request->post()) {
            $tariff->load(Yii::$app->request->post());
            // Очистим все пустые поля
            foreach ($tariff->fields() as $field) {
                if ($tariff->$field == '') {
                    $tariff->$field = null;
                }
            }

            if (is_null($tariff->total)
                && (!$tariff->additional_sum || !$tariff->additional_sum_prefix || !$tariff->additional_sum_type)
            ) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Tariff param values is empty'));
            } else {
                if ($tariff->save()) {
                    return $this->redirect(['shop/update', 'id' => $shopId]);
                }
            }
        }

        return $this->renderAjax('_add', [
            'tariff' => $tariff,
            'carrierKeys' => $carrierKeys,
            'deliveryMethods' => $deliveryMethods
        ]);
    }

    /**
     * Удаление пользовательского тарифа
     *
     * @param $shopId
     * @return string
     */
    public function actionRemove($shopId, $tariffId)
    {
        $tariff = Tariff::find()->where([
            'shop_id' => $shopId,
            'id' => $tariffId
        ])->one();

        if (Yii::$app->request->post()) {
            if (Tariff::deleteAll([
                'shop_id' => $shopId,
                'id' => $tariffId
            ])) {
                return $this->redirect(['shop/update', 'id' => $shopId]);
            }
        }

        return $this->renderAjax('_remove', [
            'tariff' => $tariff
        ]);
    }
}
