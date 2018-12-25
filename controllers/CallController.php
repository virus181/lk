<?php
namespace app\controllers;

use app\components\Clients\Call;
use app\models\search\CallSearch;
use app\models\User;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class CallController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CallSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $exportProvider = $searchModel->export(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'exportProvider' => $exportProvider,
            'context' => []
        ]);
    }

    /**
     * @return array
     */
    public function actionGetShopPhoneNumbers(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return (new Call())->getShopPhoneNumbers();
    }
}