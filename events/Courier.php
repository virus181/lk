<?php
namespace app\events;

use Yii;
use yii\base\Event;
use app\models;

class Courier extends Event
{
    const EVENT_CALL_COURIER = 'call_courier';

    /** @var models\Order */
    public $order;

    /** @var string */
    public $email;

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
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function sendOrderCreatedMessage()
    {
        foreach ($this->order->shop->users as $user){
            if ($user->notify) {
                Yii::$app
                    ->mailer
                    ->compose(
                        ['html' => 'orderCreated'],
                        ['order' => $this->order]
                    )
                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                    ->setTo($this->email)
                    ->setSubject(Yii::$app->name . ': Заказ №' . $this->order->id)
                    ->send();
            }
        }
    }

    public function prepareEvent()
    {
        Yii::$app->on(\app\events\Courier::EVENT_CALL_COURIER, function (Order $event) {
            $event->sendOrderCreatedMessage();
        });
        return $this;
    }
}
