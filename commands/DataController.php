<?php
namespace app\commands;

use app\components\DemoData;
use Yii;
use yii\console\Controller;

class DataController extends Controller
{
    /**
     * Удаление демо данных
     */
    public function actionClearDemoData()
    {
        /** @var DemoData $demo */
        $demo = Yii::$app->get('demo');
        $demo->clearDemoData();
    }
}
