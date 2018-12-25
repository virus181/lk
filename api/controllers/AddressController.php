<?php
namespace app\api\controllers;

use app\api\base\BaseActiveController;
use app\api\view\Address\Cities;
use app\api\view\Address\Suggestions;
use app\components\Clients\Dadata;
use Yii;
use yii\web\Request;
use yii\web\Response;

class AddressController extends BaseActiveController
{

    public $modelClass = 'app\models\Address';
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actionFull(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var Request $request */
        $request = Yii::$app->request;
        $suggestions = (new Dadata())->getSuggestions('address', [
            'query' => $request->get('q'),
            'limit' => $request->get('limit') ?? 10
        ]);
        return (new Suggestions())->setSuggestions($suggestions['suggestions'])->build();
    }

    /**
     * @return array
     */
    public function actionFull_v2(): array
    {
        // Заготовка для методов API Версии 2.0
    }

    /**
     * @return array
     */
    public function actionCity(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var Request $request */
        $request = Yii::$app->request;
        $suggestions = (new Dadata())->getSuggestions('address', [
            'query' => $request->get('q'),
            'from_bound' => 'city',
            'to_bound' => 'settlement',
            'location_type' => 'region',
            'location' => $request->get('l') ?? null
        ]);

        return (new Suggestions())->setSuggestions($suggestions['suggestions'])->build();
    }

    /**
     * @return array
     */
    public function actionRegion(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var Request $request */
        $request = Yii::$app->request;
        $suggestions = (new Dadata())->getSuggestions('address', [
            'query' => $request->get('q'),
            'from_bound' => 'region',
            'to_bound' => 'region'
        ]);
        return (new Suggestions())->setSuggestions($suggestions['suggestions'])->build();
    }

    /**
     * @return array
     */
    public function actionStreet(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var Request $request */
        $request = Yii::$app->request;
        $suggestions = (new Dadata())->getSuggestions('address', [
            'query' => $request->get('q'),
            'from_bound' => 'street',
            'to_bound' => 'street',
            'location_type' => 'city',
            'location' => $request->get('l') ?? null
        ]);

        return (new Suggestions())->setSuggestions($suggestions['suggestions'])->build();
    }


    /**
     * Получение подсказок по городам
     * @version 2
     * @return array
     */
    public function actionCity_v2(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var Request $request */
        $request = Yii::$app->request;
        $suggestions = (new Dadata())->getSuggestions('address', [
            'query' => $request->get('q'),
            'from_bound' => 'city',
            'to_bound' => 'settlement',
            'location_type' => 'region',
            'location' => $request->get('l') ?? null
        ]);

        return (new Cities())->setSuggestions($suggestions['suggestions'])->build();
    }
}