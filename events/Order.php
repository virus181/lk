<?php
namespace app\events;

use Yii;
use yii\base\Event;
use app\models;

class Order extends Event
{
    const EVENT_ORDER_CREATED = 'order_created';

    /** @var models\Order */
    public $order;
    /** @var boolean */
    public $isApi;

    /**
     * @param models\Order $order
     * @return $this
     */
    public function setOrder(models\Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param bool $isApi
     * @return $this
     */
    public function setIsApi(bool $isApi)
    {
        $this->isApi = $isApi;
        return $this;
    }

    public function sendOrderCreatedMessage()
    {
        foreach ($this->order->shop->users as $user){
            if ($user->notify) {
                Yii::$app
                    ->mailer
                    ->compose(
                        ['html' => 'callCourier'],
                        ['order' => $this->order]
                    )
                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                    ->setTo($user->email)
                    ->setSubject(Yii::$app->name . ': Создан новый заказ №' . $this->order->id)
                    ->send();
            }
        }
    }

    public function prepareEvent()
    {
        Yii::$app->on(\app\events\Order::EVENT_ORDER_CREATED, function (Order $event) {
            $event->sendOrderCreatedMessage();
        });
        return $this;
    }
}
