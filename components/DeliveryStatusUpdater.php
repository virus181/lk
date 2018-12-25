<?php

namespace app\components;

use app\behaviors\LogBehavior;
use app\delivery\Deliveries;
use app\models\DeliveryStatus;
use app\models\Order;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

class DeliveryStatusUpdater extends Component
{
    private $limit = 50;
    private $errorMessages = [];
    private $excludeStatuses = ['uploading', 'uploaded', 'uploadingError', 'unknown', 'problem', 'notApplicable', 'lost'];

    /**
     * Метод сохранения статусов, получает все статусы по orderId и сохраняет их
     *
     * @param Order $order
     * @return bool
     */
    private function saveOrderStatuses(Order $order)
    {
        /** @var Deliveries $deliveries */
        $deliveries = Yii::createObject(Deliveries::className(), [$order, null]);
        $data = $deliveries->getStatusHistory();
        if (!empty($data['code']) && in_array($data['code'], ['040305', '040080'])) {
            return false;
        }
        if (!empty($data['rows'])) {
            foreach ($data['rows'] as $status) {
                if ($status['key'] == 'uploading' || !$status['createdProvider']) {
                    continue;
                }

                $deliveryStatus = new DeliveryStatus();
                $deliveryStatus->order_id = $order->id;
                $deliveryStatus->status = ($status['providerName'] == '') ? $status['name'] : $status['providerName'];
                $deliveryStatus->status_key = $status['key'];
                $deliveryStatus->status_date = ($status['createdProvider'] == '') ? strtotime($status['created']) : strtotime($status['createdProvider']);
                $deliveryStatus->description = $status['providerDescription'];
                if ($deliveryStatus->validate()) {
                    $deliveryStatus->save();
                    unset($deliveryStatus);
                } else {
                    // TODO записать лог
                }
            }
        }
    }

    /**
     * Сохраняет последний статус если такого статуса еще нет
     *
     * @param array $succeedOrder
     * @param int $orderId
     */
    private function saveNewStatus($succeedOrder, $orderId)
    {
        $status = DeliveryStatus::find()
            ->andWhere(['order_id' => $orderId])
            ->orderBy(['status_date' => SORT_DESC])
            ->one();

        try {
            if (!$status || ($status && $status->status != $succeedOrder['status']['providerName'])) {
                $deliveryStatus = new DeliveryStatus();
                $deliveryStatus->order_id = $orderId;
                $deliveryStatus->status = ($succeedOrder['status']['providerName'] == '') ? $succeedOrder['status']['name'] : $succeedOrder['status']['providerName'];
                $deliveryStatus->status_key = $succeedOrder['status']['key'];
                $deliveryStatus->status_date = ($succeedOrder['status']['createdProvider'] == '') ? strtotime($succeedOrder['status']['created']) : strtotime($succeedOrder['status']['createdProvider']);
                $deliveryStatus->description = $succeedOrder['status']['providerDescription'];
                if ($deliveryStatus->validate()) {
                    $deliveryStatus->save();
                }
            }
        } catch (\Exception $e) {
            print_r($succeedOrder);
        }
    }

    /**
     * Джоба обновления статусов заказа
     */
    public function updateInSendingOrders()
    {
        $counter = 0;
        $orderQuery = Order::find()
            ->where(['in', 'status', Order::STATUS_DELIVERING])
            ->andWhere(['not', ['provider_number' => null]]);

        foreach ($orderQuery->batch($this->limit) as $orders) {
            /** @var Deliveries $deliveries */
            $deliveries = Yii::createObject(Deliveries::className(), [$orders, null]);
            $lastStatuses = $deliveries->getLastStatuses();
            if ($succeedOrders = ArrayHelper::getValue($lastStatuses, 'succeedOrders')) {
                foreach ($succeedOrders as $succeedOrder) {
                    /** Order[] $orders */
                    foreach ($orders as $order) {
                        if ($succeedOrder['orderInfo']['clientNumber'] == $order->id
                            && json_encode($order->delivery_status) != json_encode($succeedOrder['status'])
                        ) {
                            try {

                                $oldStatus = $order->status;

                                // Если статусов нет то нужно запустить процесс создания статуса
                                if (!$order->deliveryStatuses) {
                                    $this->saveOrderStatuses($order);
                                }

                                if (in_array($succeedOrder['status']['key'], $this->excludeStatuses)) {
                                    continue;
                                }

                                echo $order->id . "\n";

                                if ($succeedOrder['status']['key'] == 'deliveryCanceled') {

                                    if($order->getWorkflowStatusKey($order->status) == Order::STATUS_READY_FOR_DELIVERY) {
                                        $order->sendToStatus(Order::STATUS_WAITING_COURIER);
                                    }
                                    if($order->getWorkflowStatusKey($order->status) == Order::STATUS_WAITING_COURIER) {
                                        $order->sendToStatus(Order::STATUS_IN_DELIVERY);
                                    }
                                    $order->sendToStatus(Order::STATUS_CANCELED_AT_DELIVERY);

                                } elseif ($succeedOrder['status']['key'] == 'delivered') {

                                    if($order->getWorkflowStatusKey($order->status) == Order::STATUS_READY_FOR_DELIVERY) {
                                        $order->sendToStatus(Order::STATUS_WAITING_COURIER);
                                    }
                                    if($order->getWorkflowStatusKey($order->status) == Order::STATUS_WAITING_COURIER) {
                                        $order->sendToStatus(Order::STATUS_IN_DELIVERY);
                                    }
                                    $order->sendToStatus(Order::STATUS_DELIVERED);

                                } elseif ($succeedOrder['status']['key'] == 'readyForRecipient') {

                                    if($order->getWorkflowStatusKey($order->status) == Order::STATUS_WAITING_COURIER) {
                                        $order->sendToStatus(Order::STATUS_IN_DELIVERY);
                                    }
                                    $order->sendToStatus(Order::STATUS_READY_DELIVERY);

                                } elseif ($succeedOrder['status']['key'] == 'returned') {

                                    if($order->getWorkflowStatusKey($order->status) == Order::STATUS_IN_DELIVERY) {
                                        $order->sendToStatus(Order::STATUS_ON_RETURN);
                                    }
                                    $order->sendToStatus(Order::STATUS_RETURNED);

                                } elseif ($succeedOrder['status']['key'] == 'returnedFromDelivery'
                                    || $succeedOrder['status']['key'] == 'returning'
                                    || $succeedOrder['status']['key'] == 'returnReady'
                                ) {

                                    $order->sendToStatus(Order::STATUS_ON_RETURN);

                                } elseif ($succeedOrder['status']['key'] == 'delivering'
                                    || $succeedOrder['status']['key'] == 'onPointIn'
                                    || $succeedOrder['status']['key'] == 'onPointOut'
                                    || $succeedOrder['status']['key'] == 'onWay'
                                ) {
                                    if($order->getWorkflowStatusKey($order->status) == Order::STATUS_READY_FOR_DELIVERY) {
                                        $order->sendToStatus(Order::STATUS_WAITING_COURIER);
                                    }
                                    $order->sendToStatus(Order::STATUS_IN_DELIVERY);
                                } else {
                                    continue;
                                }

                                $dispatchNumber = null;
                                if(isset($succeedOrder['orderInfo']['providerNumber'])
                                    && $succeedOrder['orderInfo']['providerNumber'] != ''
                                ) {
                                    $dispatchNumber = $succeedOrder['orderInfo']['providerNumber'];
                                }
                                $this->saveNewStatus($succeedOrder, $order->id);
                                (new Query())->createCommand()->update(
                                    '{{%order}}',
                                    [
                                        'delivery_status' => json_encode($succeedOrder['status']),
                                        'status' => $order->status,
                                        'dispatch_number' => $dispatchNumber
                                    ],
                                    ['id' => $order->id]
                                )->execute();

                                $counter++;

                                // TODO перенести логирование в одно место так как сейчас слишком рарозненно
                                LogBehavior::setSingleLog(
                                    'status',
                                    $oldStatus,
                                    $order->status,
                                    'Order',
                                    $order->id,
                                    $order->id
                                );
                            } catch (\Throwable $e) {
                                $this->errorMessages[] = 'Order #' . $order->id . $e->getMessage();
                                Yii::error("Error no status update: \n" . $e->getMessage(), __METHOD__);
                            }
                        }
                    }
                }
            }
        }

        echo sprintf("Всего обновлено %d заказов", $counter);

        if (!empty($this->errorMessages)) {
            Yii::$app->slack->send('Update inSenging numbers', ':thumbs_up:', [
                [
                    'fallback' => 'Log message',
                    'color' => Yii::$app->slack->getLevelColor(Logger::LEVEL_ERROR),
                    'fields' => [
                        [
                            'title' => 'Application ID',
                            'value' => Yii::$app->id,
                            'short' => true,
                        ],
                        [
                            'title' => 'Count updated orders',
                            'value' => $counter,
                            'short' => true,
                        ],
                        [
                            'title' => 'Error',
                            'value' => implode('; ', $this->errorMessages),
                            'short' => true,
                        ]
                    ],
                ],
            ]);
        }
    }

    /**
     * Джоба простовления трек номеров заказов
     */
    public function updateDispatchNumbers()
    {
        $orderQuery = Order::find()
            ->where(['not in', 'status', Order::STATUS_NOT_DELIVEERY])
            ->andWhere(['dispatch_number' => null]);
        $counter = 0;

        foreach ($orderQuery->batch($this->limit) as $orders) {
            /** @var Deliveries $deliveries */
            $deliveries = Yii::createObject(Deliveries::className(), [$orders, null]);
            $lastStatuses = $deliveries->getLastStatuses();
            if ($succeedOrders = ArrayHelper::getValue($lastStatuses, 'succeedOrders')) {
                foreach ($succeedOrders as $succeedOrder) {
                    foreach ($orders as $order) {
                        if ($succeedOrder['orderInfo']['clientNumber'] == $order->id) {
                            try {
                                if(isset($succeedOrder['orderInfo']['providerNumber']) && $succeedOrder['orderInfo']['providerNumber'] != '') {
                                    $dispatchNumber = $succeedOrder['orderInfo']['providerNumber'];
                                    (new Query())->createCommand()->update(
                                        '{{%order}}',
                                        [
                                            'dispatch_number' => $dispatchNumber
                                        ],
                                        ['id' => $order->id]
                                    )->execute();

                                    // TODO перенести логирование в одно место так как сейчас слишком рарозненно
                                    LogBehavior::setSingleLog(
                                        'dispatch_number',
                                        null,
                                        $dispatchNumber,
                                        'Order',
                                        $order->id,
                                        $order->id
                                    );

                                    $counter++;
                                    echo $order->id .': ' . $succeedOrder['orderInfo']['providerNumber'] . "\n";
                                }
                            } catch (Exception $e) {
                                $this->errorMessages[] = $e->getMessage();
                                Yii::error("Error no status update: \n" . $e->getMessage(), __METHOD__);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($this->errorMessages)) {
            Yii::$app->slack->send('Update dispatch numbers', ':thumbs_up:', [
                [
                    'fallback' => 'Log message',
                    'color' => Yii::$app->slack->getLevelColor(Logger::LEVEL_ERROR),
                    'fields' => [
                        [
                            'title' => 'Application ID',
                            'value' => Yii::$app->id,
                            'short' => true,
                        ],
                        [
                            'title' => 'Count updated orders',
                            'value' => $counter,
                            'short' => true,
                        ],
                        [
                            'title' => 'Error',
                            'value' => implode('; ', $this->errorMessages),
                            'short' => true,
                        ]
                    ],
                ],
            ]);
        }
    }

    /**
     * Джоба отклонения проблемных заказов после 2х недельного простоя
     */
    public function cancelErrorOrders()
    {
        $order = new Order();
        $errorStatus = $order->getWorkflowStatusId(Order::STATUS_DELIVERY_ERROR);
        $orders = Order::find()
            ->where(['status' => $errorStatus])
            ->andWhere(['<', 'updated_at', time() - (86400 * 4)])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        foreach ($orders as $order) {
            if ($order->sendToStatus(Order::STATUS_CANCELED)) {
                (new Query())
                    ->createCommand()
                    ->update('{{%order}}', [
                        'status' => $order->status,
                    ], ['id' => $order->id])
                    ->execute();
                echo $order->id . "\n";
            }
        }

    }
}