<?php
namespace app\delivery\boxberry;

use app\models\User;
use Yii;
use yii\httpclient\Client;

class BaseBoxberry extends Client
{
    CONST CARRIER_KEY = 'boxberry';

    public $baseUrl = 'http://api.boxberry.de/json.php?s';
    private $token = '31955.rvpqfbef';
    private $_transport = 'yii\httpclient\CurlTransport';

    public function __construct($config = [])
    {
        parent::__construct($config);

        if ($this->baseUrl === null) {
            $this->baseUrl = Yii::$app->params['boxberry.baseUrl'];
        }

        $this->transport = $this->_transport;
    }

    /**
     * @param array $params
     * @param $url
     * @param $method
     * @param integer $cache
     * @return \yii\httpclient\Response
     */
    public function sendRequest($params = [], $url, $method = 'GET', $cache = 3600)
    {
        if (!$url) {
            Yii::error('Url must be defined', __METHOD__);
        }
        if (!$method) {
            Yii::error('Method type must be defined', __METHOD__);
        }

        $request = $this->createRequest();

        if (strtolower($method) !== 'get') {
            $request->setFormat(Client::FORMAT_JSON);
        }

        $request
            ->setUrl($this->baseUrl)
            ->setMethod($method)
            ->setData($params + ['token' => $this->token]);

        if ($cache === false) {
            return $request->send();
        } else {
            return Yii::$app->cache->getOrSet([$url, $params, $method], function () use ($request) {
                return $request->send();
            }, $cache);
        }
    }

    /**
     * @param $url
     * @param $params
     * @return mixed
     */
    public function replaceUrlParams($url, $params)
    {
        foreach ($params as $name => $value) {
            if (is_string($value)) {
                $url = str_replace('{' . $name . '}', $value, $url);
            }
        }

        return $url;
    }

    /**
     * @param $value object|array
     * @param bool $clearNullValue
     * @return string
     * @internal param bool $clear
     */
    function getArr($value, $clearNullValue = true)
    {
        $array = json_decode(json_encode($value), true);
        if ($clearNullValue) {
            $array = $this->clearRecursive($array);
        }
        return $array;
    }

    private function clearRecursive($array)
    {
        foreach ($array as $key => $val) {
            if (is_array($val) && !empty($val)) {
                $array[$key] = $this->clearRecursive($val);
            } else if ((!is_array($val) && $val === null) || (is_array($val) && empty($val))) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}