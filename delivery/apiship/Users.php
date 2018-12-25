<?php
namespace app\delivery\apiship;

use Yii;
use yii\httpclient\Client;

class Users extends Client
{
    public $baseUrl = 'https://api.apiship.ru/v1';
    public $login = 'fastery';
    public $password = 'c28dY6Eaa4';
    public $method = 'post';
    public $url = 'login';

    private $_transport = 'yii\httpclient\CurlTransport';

    public function __construct($config = [])
    {
        parent::__construct($config);

        if ($this->baseUrl === null) {
            $this->baseUrl = Yii::$app->params['apiship.baseUrl'];
        }
        if ($this->login === null) {
            $this->login = Yii::$app->params['apiship.login'];
        }
        if ($this->password === null) {
            $this->password = Yii::$app->params['apiship.password'];
        }

        $this->transport = $this->_transport;
    }

    /**
     * @return \yii\httpclient\Response
     */
    public function login()
    {
        $data = [
            'login' => $this->login,
            'password' => $this->password,
        ];

        return Yii::$app->cache->getOrSet($data, function () use ($data) {
            return $this->createRequest()
                ->setUrl($this->url)
                ->setFormat(Client::FORMAT_JSON)
                ->setMethod($this->method)
                ->setData($data)
                ->send();
        }, 170000);
    }
}