<?php

namespace app\commands;

use app\behaviors\PointSaver;
use app\delivery\apiship\Delivery;
use app\models\CityPickPoint;
use app\models\DeliveryService;
use app\models\Points;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class ApishipController extends Controller
{
    public function actionUpdatePoints()
    {
        $apiShip = new Delivery();

        $points = $apiShip->getListPoints(['limit' => 100000]);
        foreach ($points as $point) {
            $pointModel = new Points();

            $pointModel->name = ArrayHelper::getValue($point, 'name');
            $pointModel->point_id = ArrayHelper::getValue($point, 'id');
            $pointModel->carrier_key = ArrayHelper::getValue($point, 'providerKey');
            $pointModel->code = Delivery::className() . '.' . $pointModel->carrier_key . '.' . $pointModel->point_id;
            $pointModel->additional_code = ArrayHelper::getValue($point, 'code');
            $pointModel->cod = (int)ArrayHelper::getValue($point, 'cod');
            $pointModel->card = (int)ArrayHelper::getValue($point, 'paymentCard');
            $pointModel->type = (int)ArrayHelper::getValue($point, 'type');
            $pointModel->available_operation = (int)ArrayHelper::getValue($point, 'availableOperation');

            $pointModel->address =
                ArrayHelper::getValue($point, 'city') . ', ' .
                ArrayHelper::getValue($point, 'streetType') . '. ' .
                ArrayHelper::getValue($point, 'street') . ', д. ' .
                ArrayHelper::getValue($point, 'house') .
                (ArrayHelper::getValue($point, 'office') ? ', кв. ' . ArrayHelper::getValue($point, 'office') : '');
            $pointModel->city_guid = ArrayHelper::getValue($point, 'cityGuid');

            $pointModel->phone = ArrayHelper::getValue($point, 'phone');
            $pointModel->timetable = ArrayHelper::getValue($point, 'timetable');

            $pointModel->lat = ArrayHelper::getValue($point, 'lat');
            $pointModel->lng = ArrayHelper::getValue($point, 'lng');

            $pointModel->class_name = Delivery::className();

            $saver = Yii::createObject(PointSaver::className(), [$pointModel]);

            $saver->save();
        }
    }

    /**
     * Обновление дополнительных услуг СД
     */
    public function actionUpdateServices()
    {
        $deliveries = \app\models\Delivery::find()->all();
        foreach ($deliveries as $delivery) {

            $apiShip = new Delivery();
            $services = $apiShip->getListServices($delivery->carrier_key);

            if (!empty($services)) {
                $isSuccess = true;
                $transaction = Yii::$app->db->beginTransaction();

                \app\models\DeliveryService::deleteAll(['delivery_id' => $delivery->id]);
                foreach ($services as $srv) {

                    $service = new DeliveryService();
                    echo $delivery->id . "\n";
                    $service->delivery_id = $delivery->id;
                    $service->name = $service->getService($srv['extraParamName'])
                        ? Yii::t('delivery', $service->getService($srv['extraParamName']))
                        : 'Неизвестная услуга';
                    $service->type = $service->getService($srv['extraParamName']);
                    $service->service_key = $srv['extraParamName'];
                    $service->description = $srv['description'];

                    if (!$service->save()) {
                        $isSuccess = false;
                        print_r($service->errors);
                    }
                }

                if (!$isSuccess) {
                    echo "Updated with errors \n";
                    $transaction->rollBack();
                } else {
                    echo "Success updated \n";
                    $transaction->commit();
                }
            }

        }
    }

    /**
     * Обновление городов PP
     */
    public function actionPickpointCities()
    {
        $apiShip = new Delivery();
        $cities = $apiShip->getPickPointCities();

        if (!empty($cities['rows'])) {
            $isSuccess = true;
            $transaction = Yii::$app->db->beginTransaction();

            \app\models\DeliveryService::deleteAll();
            foreach ($cities['rows'] as $city) {

                echo $city['fullName'] . "\n";
                $cityPP = new CityPickPoint();
                $cityPP->id = (int) $city['id'];
                $cityPP->name = $city['cityName'];
                $cityPP->region = $city['regionName'];
                $cityPP->owner_id = $city['ownerId'];
                $cityPP->city_fias_id = $city['cityGuid'];
                $cityPP->full_name = $city['fullName'];
                $cityPP->code = $city['code'];

                if (!$cityPP->save()) {
                    $isSuccess = false;
                    print_r($cityPP->errors);
                }
            }

            if (!$isSuccess) {
                echo "Updated with errors \n";
                $transaction->rollBack();
            } else {
                echo "Success updated \n";
                $transaction->commit();
            }
        }
    }
}