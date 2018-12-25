<?php
namespace app\delivery\apiship;


use yii\base\InvalidConfigException;

class Autocomplete extends BaseApiShip
{

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function region($params = [], $url = 'autocomplete/region/{query}', $method = 'get') {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function area($params = [], $url = 'autocomplete/area/{query}', $method = 'get') {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function city($params = [], $url = 'autocomplete/city/{query}', $method = 'get') {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function street($params = [], $url = 'autocomplete/street/{query}', $method = 'get') {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param $params
     * @param $url
     * @param $method
     * @return \yii\httpclient\Response
     * @throws InvalidConfigException
     */
    public function sendRequest($params = [], $url, $method)
    {
        if (!isset($params['query']) || $params['query'] === '') {
            throw new InvalidConfigException('Params \'query\' must be defined', __METHOD__);
        }
        return parent::sendRequest($params, $url, $method);
    }
}