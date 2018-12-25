<?php
namespace app\delivery\apiship;

class Status extends BaseApiShip
{
    public $orderIds;
    public $orderId;

    /**
     * @return \yii\httpclient\Response
     */
    public function lastStatuses()
    {
        if (!is_array($this->orderIds)) {
            $this->orderIds = (array)$this->orderIds;
        }

        return $this->sendRequest($this->getArr($this), 'orders/statuses', 'post');
    }

    /**
     * @return \yii\httpclient\Response
     */
    public function statusHistory()
    {
        return $this->sendRequest(
            $this->getArr($this),
            'orders/' . $this->orderId . '/statusHistory',
            'get'
        );
    }

    /**
     * Получение текущего статуса
     * @param int|boolean $cache
     * @return \yii\httpclient\Response
     */
    public function currentStatus($cache = false)
    {
        return $this->sendRequest([
            'clientNumber' => $this->orderId,
        ], 'orders/status', 'get', $cache);
    }
}