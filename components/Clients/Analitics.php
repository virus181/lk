<?php
namespace app\components\Clients;

use Yii;
use yii\httpclient\Client;

class Analitics extends Client
{
    public $baseUrl = 'http://94.250.251.2:8086/write?db=grafana';
    private $_transport = 'yii\httpclient\CurlTransport';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->transport = $this->_transport;
    }

    /**
     * @param array  $params
     * @param int    $value
     * @param string $db
     * @param int    $time
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function sendRequest($params = [], $value, $db, $time = 0, $method = 'GET')
    {
        if (!$db) {
            Yii::error('DB must be defined', __METHOD__);
        }
        if (!$method) {
            Yii::error('Method type must be defined', __METHOD__);
        }

        $request = $this->createRequest();

        $paramString = '%s,%s value=%d%s';
        $tags = [];
        foreach ($params as $key => $param) {
            $tags[] = $key . '=' . $param;
        }

        $request
            ->setUrl($this->baseUrl)
            ->setMethod($method)
            ->setContent(sprintf($paramString, $db, implode(',', $tags), $value, $time ? ' '. $time : ''));

        return $request->send();
    }
}