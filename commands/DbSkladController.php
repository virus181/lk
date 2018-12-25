<?php
namespace app\commands;

use app\components\SkladSynchronizerProduct;
use app\models\User;
use Yii;
use yii\console\Controller;
use yii\di\Instance;

class DbSkladController extends Controller
{
    public $skladSynchronizerProduct = 'skladSynchronizerProduct';

    public function actionUpdateShopsAndProducts()
    {
        Yii::$app->user->setIdentity(User::findOne(1));
        /** @var SkladSynchronizerProduct $skladSynchronizerProduct */
        $skladSynchronizerProduct = Instance::ensure($this->skladSynchronizerProduct, SkladSynchronizerProduct::className());
        $skladSynchronizerProduct->sinchronize();
    }

    public function actionUpdateProducts()
    {
        Yii::$app->user->setIdentity(User::findOne(1));
        /** @var SkladSynchronizerProduct $skladSynchronizerProduct */
        $skladSynchronizerProduct = Instance::ensure($this->skladSynchronizerProduct, SkladSynchronizerProduct::className());
        $skladSynchronizerProduct->sinchronizeV2();
    }
}