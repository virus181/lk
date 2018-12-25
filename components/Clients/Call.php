<?php
namespace app\components\Clients;

use app\models\helper\Phone;
use app\models\Shop;
use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use \yii\httpclient\Client;

class Call extends Client
{
    public $baseUrl = 'https://asterisk.fidoman.ru/cgi-bin';
    private $_transport = 'yii\httpclient\CurlTransport';

    public function __construct($config = [])
    {
        parent::__construct($config);
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

        $url = $this->replaceUrlParams($url, $params);
        $request = $this->createRequest();

        if (strtolower($method) !== 'get') {
            $request->setFormat(Client::FORMAT_JSON);
        }

        $request
            ->setUrl($url)
            ->setMethod($method)
            ->setData($params);

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
     * @return array
     */
    public function getShopPhoneNumbers(): array
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $shopIds = $user->getAllowedShopIds();

        if (empty($shopIds)) {
            return [];
        }

        $shopIdMap = ArrayHelper::map(
            Shop::find()
                ->where(['id' => $shopIds, 'fulfillment' => 1])
                ->andWhere(['NOT', ['additional_id' => null]])
                ->asArray()
                ->all(),
            'id',
            'additional_id'
        );

        $phones = [];
        foreach ($shopIdMap as $id) {
            $params = [
                'what' => 'get_shop',
                'shop_id' => $id
            ];
            $data = $this->sendRequest([], 'data.py?' . http_build_query($params), 'GET', 86400);
            $shopInfo = $data->getData();
            $phones[] = (new Phone($shopInfo[0]['shop_phone']))->getHumanView();
        }
        return $phones;
    }
}