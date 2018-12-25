<?php
namespace app\delivery\apiship;

class PrintForm extends BaseApiShip
{
    public $orderIds;
    public $format = 'pdf';

    /**
     * @return \yii\httpclient\Response
     */
    public function getLabels()
    {
        return $this->sendRequest($this->getArr($this), 'orders/labels', 'post', false);
    }

    /**
     * @return \yii\httpclient\Response
     */
    public function getRegistries()
    {
        return $this->sendRequest($this->getArr($this), 'orders/waybills', 'post', false);
    }
}