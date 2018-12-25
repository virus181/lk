<?php
namespace app\delivery\apiship;

use app\components\Stat;
use app\delivery\BaseClient;
use app\models\User;
use Yii;
use yii\httpclient\Client;
use yii\httpclient\Request;

class BaseApiShip extends BaseClient
{
    public $token = '';
    public $baseUrl = 'https://api.apiship.ru/v1';

    public function __construct($config = [])
    {
        parent::__construct($config);

        if ($this->baseUrl === null) {
            $this->baseUrl = Yii::$app->params['apiship.baseUrl'];
        }

        $this->transport = $this->_transport;

        if (!$this->token) {
            Yii::error('Params $apiKey must be defined', __METHOD__);
        }
    }

    /**
     * @param array $params
     * @param $url
     * @param $method
     * @param integer $cache
     * @param array $cacheParams
     * @return \yii\httpclient\Response
     */
    public function sendRequest(
        $params = [],
        $url,
        $method,
        $cache = 3600,
        $cacheParams = []
    ) {
        if (!$url) {
            Yii::error('Url must be defined', __METHOD__);
        }
        if (!$method) {
            Yii::error('Method type must be defined', __METHOD__);
        }

        $url = $this->replaceUrlParams($url, $params);

        $request = $this->createRequest();

        if (strtolower($method) !== 'get') {
            $request->setFormat(Client::FORMAT_JSON);
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $token = $user ? $user->getAccessToken() : null;

        // TODO исправить эту штуку
        foreach ($params as $key=>$param) {
            if ($url == 'courierCall') {
                if (!in_array($key, ['providerKey','date','timeStart','timeEnd','weight','width','height','length','orderIds','postIndex','countryCode','region','area','city','cityGuid','street','house','block','office','companyName','contactName','phone','email'])) {
                    unset($params[$key]);
                }
            }
        }

        $request
            ->setUrl($url)
            ->setMethod($method)
            ->setData($params)
            ->setHeaders(['Authorization' => $this->token]);

        if ($url !== 'calculator') {
            Yii::info([
                'url' => $url,
                'method' => $method,
                'params' => $params,
            ], 'apiship-request');
        }

        Yii::info([
            'json' => json_encode($params),
        ], 'apiship-request');

        $cParams = !empty($cacheParams) ? $cacheParams : $params;

        if ($cache === false) {
            $result = $request->send();
        } else {
            $result = Yii::$app->cache->getOrSet([$url, $cParams, $method, $token], function () use ($request) {
                return $request->send();
            }, $cache);
        }

        Yii::info([
            'result' => $result->getData(),
        ], 'apiship-response');

        if ($result->getStatusCode() >= 400) {
            // Отправим ошибку в сбор статистики
            /** @var Stat $stat */
            $stat = Yii::$app->get('stat');
            $stat->sendErrorEvent([
                'code' => $result->getStatusCode(),
                'message' => 'Ошибка запроса' . $url,
                'userId' => $user ? $user->getId() : 0,
                'type' => 'apiship'
            ]);
        }

        return $result;
    }

    /**
     * @param array $params
     * @param $url
     * @param $method
     * @return Request
     */
    public function prepareRequest(
        $params = [],
        $url,
        $method
    ) {
        if (!$url) {
            Yii::error('Url must be defined', __METHOD__);
        }
        if (!$method) {
            Yii::error('Method type must be defined', __METHOD__);
        }

        $url = $this->replaceUrlParams($url, $params);

        $request = $this->createRequest();

        if (strtolower($method) !== 'get') {
            $request->setFormat(Client::FORMAT_JSON);
        }

        $request
            ->setUrl($url)
            ->setMethod($method)
            ->setData($params)
            ->setHeaders(['Authorization' => $this->token]);

        return $request;
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