<?php
namespace app\components\Clients;

use Yii;
use \yii\httpclient\Client;

class Dadata extends Client
{
    public $baseUrl = 'http://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/';
    private $_transport = 'yii\httpclient\CurlTransport';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->transport = $this->_transport;
    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        return array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Token ' . Yii::$app->params['dadata.token']
        );
    }

    /**
     * @param string $type
     * @param array $query
     * @param int $cache
     * @return mixed|\yii\httpclient\Response
     */
    public function getSuggestions(string $type, array $query, $cache = 86400000)
    {
        $request = $this->createRequest();

        $params = [
            'query' => $query['query'],
            'count' => isset($query['limit']) ? $query['limit'] : 10
        ];

        if (isset($query['from_bound'])) {
            $params['from_bound']['value'] = $query['from_bound'];
        }

        if (isset($query['to_bound'])) {
            $params['to_bound']['value'] = $query['to_bound'];
        }

        if (!empty($query['location'])) {
            $ls = explode(' ', $query['location']);
            foreach ($ls as $l) {
                $params['locations'][] = [
                    $query['location_type'] => $l
                ];
            }
        }

        $request
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders($this->getHeaders())
            ->setUrl($this->baseUrl . $type)
            ->setMethod('POST')
            ->setData($params);

        if ($cache === false) {
            return $request->send()->getData();
        } else {
            return Yii::$app->cache->getOrSet(['many', $params, $this->baseUrl . $type], function () use ($request) {
                return $request->send()->getData();
            }, $cache);
        }
    }

    /**
     * @param string $city
     * @return array
     */
    public function getCity(string $city): array
    {
        $suggestions = $this->getSuggestions('address', [
            'query' => $city,
            'from_bound' => 'city',
            'to_bound' => 'settlement',
            'location_type' => 'region'
        ]);
        return isset($suggestions['suggestions'][0]) ? $suggestions['suggestions'][0] : [];
    }
}