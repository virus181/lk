<?php declare(strict_types=1);
namespace app\components\Clients;

use app\components\Clients\Accounting\Mocks\Registry;
use Yii;
use yii\httpclient\Client;
use yii\httpclient\Response;

class Accounting extends Client
{
    public $baseUrl = 'http://vpn.fastery.ru';
    public $_transport = 'yii\httpclient\CurlTransport';

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @return Response
     */
    public function sendRequest(string $url = '', array $params = [], string $method = 'GET'): Response
    {
        if (!$url) {
            Yii::error('Url must be defined', __METHOD__);
        }
        if (!$method) {
            Yii::error('Method type must be defined', __METHOD__);
        }

        $request = $this->createRequest();

        $request
            ->setUrl($url)
            ->setMethod($method)
            ->setData($params);

        $request->setHeaders([
            'Authorization' => sprintf(
                'Basic %s',
                base64_encode(
                    sprintf(
                        '%s:%s',
                        Yii::$app->params['1CLogin'],
                        Yii::$app->params['1CPassword']
                    )
                )
            )
        ]);

        if (YII_ENV == 'test') {
            $response = new Response();
            $response->setContent(json_encode((new Registry())->getLatestRegistry()));
            return $response;
        }

        return $request->send();
    }
}