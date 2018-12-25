<?php
namespace app\commands;

use app\components\Call;
use Yii;
use yii\console\Controller;

class CallController extends Controller
{
    public function actionUpdateCallList()
    {
        /** @var Call $calls */
        $calls = Yii::$app->get('call');
        $calls->updateCallList();
    }
}
