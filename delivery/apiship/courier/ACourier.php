<?php
namespace app\delivery\apiship\courier;

use app\models\Courier;
use app\models\Order;
use yii\db\Query;

abstract class ACourier
{
    /**
     * @var int
     */
    protected $pickupDate;
    /**
     * @var string
     */
    protected $pickupTimeStart;
    /**
     * @var string
     */
    protected $pickupTimeEnd;
    /**
     * @var int
     */
    protected $warehouseId;
    /**
     * @var Order[]
     */
    protected $orders;
    /**
     * @var string
     */
    protected $classNameProvider;

    /**
     * @param string $pickupDate
     * @param string $pickupTimeStart
     * @param string $pickupTimeEnd
     * @param int $warehouseId
     * @param Order[] $orders
     * @param string $classNameProvider
     */
    public function __construct(
        string $pickupDate,
        string $pickupTimeStart,
        string $pickupTimeEnd,
        int $warehouseId,
        array $orders,
        string $classNameProvider
    ) {
        $this->pickupDate = strtotime($pickupDate);
        $this->pickupTimeStart = $pickupTimeStart;
        $this->pickupTimeEnd = $pickupTimeEnd;
        $this->warehouseId = $warehouseId;
        $this->orders = $orders;
        $this->classNameProvider = $classNameProvider;
    }

    /**
     * @param Order[] $orders
     * @param array $shopCouriers
     */
    protected function updateOrders(array $orders, array $shopCouriers = [])
    {
        /** @var Order $order */
        foreach ($orders as $order) {
            // Переведем заказ в статус "Ожидает курьера"
            $order->sendToStatus(Order::STATUS_WAITING_COURIER);
            $params = [
                'status' => $order->status,
                'updated_at' => time()
            ];
            if (isset($shopCouriers[$order->shop_id])) {
                $params['courier_id'] = $shopCouriers[$order->shop_id];
            }
            (new Query())->createCommand()->update(
                '{{%order}}',
                $params,
                ['id' => $order->id]
            )->execute();
        }
    }

    /**
     * @param bool $isNeedCall
     * @param Courier[] $calledCourierIds
     * @return array
     */
    protected function getShopOrders(bool $isNeedCall, array $calledCourierIds): array
    {
        foreach ($this->orders as $order) {
            $shopOrders[$order->shop_id]['orders'][] = $order;
            $shopOrders[$order->shop_id]['isCalled'] = isset($calledCourierIds[$order->shop_id]);
            $shopOrders[$order->shop_id]['isNeedCall'] = $isNeedCall;
        }

        return $shopOrders ?? [];
    }

    abstract public function call();
}