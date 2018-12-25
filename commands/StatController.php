<?php
namespace app\commands;

use app\components\Stat;
use Yii;
use yii\console\Controller;

class StatController extends Controller
{
    public function actionSetOrderStat()
    {
        /** @var Stat $stat */
        $stat = Yii::$app->get('stat');
        $stat->setOrderStat();
    }

    public function actionSetTotalOrderStat()
    {
        /** @var Stat $stat */
        $stat = Yii::$app->get('stat');
        $stat->setTotalOrderStat();
    }

    public function actionSetOrderStatPerDay()
    {
        /** @var Stat $stat */
        $stat = Yii::$app->get('stat');
        $stat->setOrderStat(time() - 86400);
    }

    public function actionSetShopStat()
    {
        /** @var Stat $stat */
        $stat = Yii::$app->get('stat');
        $stat->setShopsStat();
    }
}
