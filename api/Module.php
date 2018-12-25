<?php
namespace app\api;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

class Module extends \yii\base\Module
{

    public function init()
    {
        /** @var Request $request */
        $request = Yii::$app->request;

        // TODO подумать возможно имеется более элегантное решение.
        $data = parse_url($request->getReferrer());
        Yii::$app->user->enableSession = ($request->getReferrer()
            && $data['host'] == $_SERVER['SERVER_NAME']);

        $authenticator = new CompositeAuth();
        $authenticator->authMethods    = [
            \yii\filters\auth\HttpBasicAuth::className(),
            \yii\filters\auth\QueryParamAuth::className()
        ];

        /** @var User $user */
        $user = Yii::$app->user;
        /** @var Response $response */
        $response = Yii::$app->response;
        $authenticator->authenticate($user, $request, $response);
        parent::init();
    }
}