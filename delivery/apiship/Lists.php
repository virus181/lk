<?php
namespace app\delivery\apiship;

use yii\base\InvalidConfigException;

class Lists extends BaseApiShip
{

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function tariffs($params = [], $url = 'lists/tariffs', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @return \yii\httpclient\Response
     */
    public function statuses($params = [], $url = 'lists/statuses', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function providers($params = [], $url = 'lists/providers', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @return \yii\httpclient\Response
     * @throws InvalidConfigException
     */
    public function providersParams($params = [], $url = 'lists/providers/{providerKey}/params', $method = 'get')
    {
        if (!isset($params['providerKey']) || $params['providerKey'] === '') {
            throw new InvalidConfigException('Params providerKey must be defined', __METHOD__);
        }

        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function points($params = [], $url = 'lists/points', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function pickupTypes($params = [], $url = 'lists/pickupTypes', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function deliveryTypes($params = [], $url = 'lists/deliveryTypes', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function paymentMethods($params = [], $url = 'lists/paymentMethods', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function operationTypes($params = [], $url = 'lists/operationTypes', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function pointTypes($params = [], $url = 'lists/pointTypes', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function services($params = [], $url = 'lists/services', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method, false);
    }

    /**
     * @param array $params
     * @param string $url
     * @param string $method
     * @return \yii\httpclient\Response
     */
    public function pickPointCities($params = [], $url = 'lists/providerCities/pickpoint', $method = 'get')
    {
        return $this->sendRequest($params, $url, $method, false);
    }
}

