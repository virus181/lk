<?php

namespace app\models\forms;

use app\delivery\apiship\courier\ACourier;
use app\delivery\apiship\courier\B2CPL;
use app\delivery\apiship\courier\BoxBerry;
use app\delivery\apiship\courier\Cdek;
use app\delivery\apiship\courier\CourierInterface;
use app\delivery\apiship\courier\Dalli;
use app\delivery\apiship\courier\Dastavista;
use app\delivery\apiship\courier\Easyway;
use app\delivery\apiship\courier\IML;
use app\delivery\apiship\courier\MaxiPost;
use app\delivery\apiship\courier\OnTime;
use app\delivery\apiship\courier\PickPoint;
use app\delivery\apiship\courier\Viehali;
use app\delivery\DeliveryHelper;
use app\models\Common\ShopDelivery;
use app\models\Courier;
use app\models\Order;
use Yii;
use yii\base\Model;
use yii\base\UserException;
use yii\db\Query;

/**
 * @property array $orderIds
 * @property array $carriers
 */
class OrdersCourierCall extends Model
{
    public $pickup_date;
    private $_orderIds = [];
    private $_carriers = [];

    public function init()
    {
        if (date('N', time()) == 5) {
            $this->pickup_date = date('d.m.Y', strtotime("+3 days"));
        } else {
            $this->pickup_date = date('d.m.Y', strtotime("+1 days"));
        }
    }

    public function rules()
    {
        return [
            [['orderIds'], 'safe'],
            [['pickup_date'], 'default', 'value' => function () {
                if (date('N', time()) == 5) {
                    return date('d.m.Y', strtotime("+3 days"));
                } else {
                    return date('d.m.Y', strtotime("+1 days"));
                }
            }],
            [['pickup_date'], 'date', 'format' => 'php:d.m.Y'],
            [['pickup_date'], 'validatePickupDate'],
        ];
    }

    public function validatePickupDate($attribute)
    {
        $dayOfWeek = date('N', strtotime($this->pickup_date));
        if ($dayOfWeek > 5 || $dayOfWeek < 1) {
            $this->addError($attribute, Yii::t('app', 'Указана некорректная дата отгрузки'));
        }
    }

    public function attributeLabels()
    {
        return [
            'pickup_date' => Yii::t('app', 'Pickup Date'),
        ];
    }

    public function getOrderIds()
    {
        $orderIds = [];
        foreach ($this->_orderIds as $orderId) {
            $orderIds[$orderId] = $orderId;
        }
        return $orderIds;
    }

    public function setOrderIds($orderIds)
    {
        if (is_string($orderIds)) {
            $orderIds = [$orderIds => $orderIds];
        }
        $this->_orderIds = $orderIds;
        $this->_carriers = [];

        return $this->_orderIds;
    }

    /**
     * @return Courier[]
     * @throws \Exception
     */
    public function call()
    {
        $couriers = [];

        if ($this->validate()) {
            foreach ($this->getCarriers() as $warehouse) {
                foreach ($warehouse['carrier_keys'] as $carrierKey => $orderIds) {

                    $orders = Order::find()
                        ->joinWith('delivery')
                        ->andWhere(['order.id' => $orderIds])
                        ->andWhere(['order_delivery.carrier_key' => $carrierKey])
                        ->all();

                    if ($orders) {
                        $shopId = $orders[0]->shop_id;
                        $shopDelivery = new ShopDelivery($shopId, $carrierKey);
                        $courier = $this->getCourier(
                            $carrierKey,
                            $this->pickup_date,
                            $shopDelivery->getPickupTimeStart(),
                            $shopDelivery->getPickupTimeEnd(),
                            $warehouse['warehouse_id'],
                            $orders,
                            $warehouse['class_name_provider']
                        );

                        $calledCouriers = $courier->call();
                        $couriers = array_merge($couriers, $calledCouriers);
                    }
                }
            }
        }

        return $couriers;
    }

    public function getCarriers()
    {
        $map = [];
        if ($this->_carriers == []) {
            $orders = (new Query)
                ->select(['warehouse.id as warehouse_id', 'warehouse.name as warehouse', 'warehouse_address.full_address as warehouse_address', 'order_delivery.class_name_provider', 'order_delivery.carrier_key', 'order.id as order_id'])
                ->from(['order'])
                ->andWhere(['order.id' => $this->_orderIds])
                ->andWhere('order_delivery.carrier_key IS NOT NULL')
                ->andWhere('warehouse.id IS NOT NULL')
                ->andWhere('order.courier_id IS NULL')
                ->join('LEFT JOIN', 'warehouse', 'warehouse.id = order.warehouse_id')
                ->join('LEFT JOIN', 'address as warehouse_address', 'warehouse.address_id = warehouse_address.id')
                ->join('LEFT JOIN', 'order_delivery', 'order_delivery.order_id = order.id')
                ->all();

            foreach ($orders as $order) {
                $map[$order['warehouse']]['warehouse_address'] = $order['warehouse_address'];
                $map[$order['warehouse']]['warehouse_id'] = $order['warehouse_id'];
                $map[$order['warehouse']]['class_name_provider'] = $order['class_name_provider'];
                if (isset($map[$order['warehouse']]['carrier_keys'][$order['carrier_key']])) {
                    $map[$order['warehouse']]['carrier_keys'][$order['carrier_key']][] = $order['order_id'];
                } else {
                    $map[$order['warehouse']]['carrier_keys'][$order['carrier_key']] = [$order['order_id']];
                }
            }
            $this->_carriers = $map;
        }

        return $this->_carriers;
    }

    /**
     * @param string $carrierKey
     * @param string $pickupDate
     * @param string $pickupTimeStart
     * @param string $pickupTimeEnd
     * @param int $warehouseId
     * @param array $orders
     * @param string $classNameProvider
     * @return ACourier
     * @throws \Exception
     */
    private function getCourier(
        string $carrierKey,
        string $pickupDate,
        string $pickupTimeStart,
        string $pickupTimeEnd,
        int $warehouseId,
        array $orders,
        string $classNameProvider
    ) {
        if ($carrierKey == DeliveryHelper::CARRIER_CODE_CDEK) {
            return new Cdek($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_B2CPL) {
            return new B2CPL($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_BOXBERRY) {
            return new BoxBerry($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_MAXI) {
            return new MaxiPost($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_DOSTAVISTA) {
            return new Dastavista($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_DALLI) {
            return new Dalli($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_IML) {
            return new IML($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_PICKPOINT) {
            return new PickPoint($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_EASYWAY) {
            return new Easyway($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_ONTIME) {
            return new OnTime($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } elseif ($carrierKey == DeliveryHelper::CARRIER_CODE_VIEHALI) {
            return new Viehali($pickupDate, $pickupTimeStart, $pickupTimeEnd, $warehouseId, $orders, $classNameProvider);
        } else {
            throw new \Exception('Данная служба не поддерживает вызов курьера');
        }
    }
}