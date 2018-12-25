<?php
namespace app\delivery\apiship\courier;

use app\delivery\DeliveryHelper;
use app\models\Courier;
use Yii;

class BoxBerry extends ACourier
{
    const CARRIER_KEY = DeliveryHelper::CARRIER_CODE_BOXBERRY;

    /**
     * @return array
     * @throws \Exception
     */
    public function call(): array
    {
        $couriers = [];

        // Получаем ID вызванных курьеров
        $calledCourierIds = (new Courier())->getActiveCourierCall(
            $this->warehouseId,
            self::CARRIER_KEY,
            $this->pickupDate,
            $this->orders
        );

        $shopCouriers = [];
        $shopOrders = $this->getShopOrders(empty($calledCourierIds), $calledCourierIds);

        foreach ($shopOrders as $shopId => $shopOrder) {
            if ($shopOrder['isNeedCall'] || !$shopOrder['isCalled']) {

                $courier = new Courier();
                $courier->orders = $shopOrder['orders'];
                $courier->pickup_date = $this->pickupDate;
                $courier->pickup_time_start = $this->pickupTimeStart;
                $courier->pickup_time_end = $this->pickupTimeEnd;
                $courier->warehouse_id = $this->warehouseId;
                $courier->carrier_key = self::CARRIER_KEY;
                $courier->class_name_provider = $this->classNameProvider;

                if (!$shopOrder['isNeedCall']) {
                    $mainCourier = $calledCourierIds['main'];

                    $courier->registry_label_url = $mainCourier->registry_label_url;
                    $courier->courier_call = $mainCourier->courier_call;
                    $courier->number = $mainCourier->number;
                    $courier->main_courier_id = $mainCourier->id;
                }

                if ($shopOrder['isNeedCall']) {
                    $transaction = Yii::$app->db->beginTransaction();
                    $courier->save();
                    try {
                        if ($courier->call()) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    }

                    try {
                        $courier->setRegistries();
                    } catch (\Exception $e) {
                        //throw $e;
                    }
                } else {
                    $courier->save();
                }

            } else {
                // Запишим уже вызванных курьеров в массив
                $courier = $calledCourierIds[$shopId];
                $shopCouriers[$shopId] = $courier->id;
            }

            $this->updateOrders($shopOrder['orders'], $shopCouriers);
            $couriers[] = $courier;
        }
        return $couriers;
    }
}