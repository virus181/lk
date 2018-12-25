<?php
namespace app\components;

use app\delivery\Deliveries;
use app\models\Order;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

class DeliveryErrorFixer extends Component
{
    private $errorMessages = [];

    public function fixAllOrders($id = null)
    {
        $counter = 0;
        $order = new Order();
        $errorStatus = $order->getWorkflowStatusId(Order::STATUS_DELIVERY_ERROR);
        $collectStatus = $order->getWorkflowStatusId(Order::STATUS_IN_COLLECTING);
        if ($id === null) {
            $orders = Order::find()
                ->where(['status' => $errorStatus])
                ->orWhere(['AND', ['status' => $collectStatus], ['NOT', ['provider_number' => null]], ['dispatch_number' => null]])
                ->orderBy(['id' => SORT_DESC])
                ->limit(100)
                ->all();
        } else {
            $orders = Order::find()->where(['status' => $errorStatus, 'id' => $id])->all();
        }

        /** @var Deliveries $deliveries */
        $deliveries = Yii::createObject(Deliveries::className(), [$orders, null]);
        $lastStatuses = $deliveries->getLastStatuses();

        if ($succeedOrders = ArrayHelper::getValue($lastStatuses, 'succeedOrders')) {
            foreach ($succeedOrders as $succeedOrder) {
                //print_r($succeedOrder);
                foreach ($orders as $order) {
                    try {

                        if ($order->provider_number === $succeedOrder['orderInfo']['orderId']
                            && $succeedOrder['orderInfo']['providerNumber'] != null
                            && empty($order->dispatch_number)
                        ) {
                            (new Query())
                                ->createCommand()
                                ->update('{{%order}}', [
                                    'dispatch_number' => $succeedOrder['orderInfo']['providerNumber']
                                ], ['id' => $order->id])
                                ->execute();
                        }

                        if ($order->status == $collectStatus) {
                            continue;
                        }

                        $sendStatusName = Order::STATUS_READY_FOR_DELIVERY;

                        if ($order->provider_number === $succeedOrder['orderInfo']['orderId']
                            && $succeedOrder['orderInfo']['providerNumber'] != null
                            && $order->sendToStatus($sendStatusName)
                        ) {
                            (new Query())
                                ->createCommand()
                                ->update('{{%order}}', [
                                    'status' => $order->status,
                                    'delivery_status' => json_encode($succeedOrder['status'])
                                ], ['id' => $order->id])
                                ->execute();
                            $counter++;
                        }

                        // Запишем статус заказа для отображение проблемного состояния для администаторов
                        if (is_null($succeedOrder['orderInfo']['providerNumber'])) {
                            (new Query())
                                ->createCommand()
                                ->update('{{%order}}', ['delivery_status' => json_encode($succeedOrder['status'])], ['id' => $order->id])
                                ->execute();
                        }
                    } catch (Exception $e) {
                        $this->errorMessages[] = $e->getMessage();
                        echo "Error no fixer error status: \n" . $e->getMessage();
                        Yii::error("Error no fixer error status: \n" . $e->getMessage(), __METHOD__);
                    }
                }
            }
        }

        if (!empty($this->errorMessages)) {
            Yii::$app->slack->send('Fix delivery error orders', ':thumbs_up:', [
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
}