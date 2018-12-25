<?php
use app\widgets\Html;
use app\widgets\YandexMap;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;

/**
 * @var $order \app\models\search\OrderSearch
 * @var $shops \app\models\Shop[]
 * @var $carriers \app\models\Delivery[]
 * @var $deliveryMethods array
 */

$this->title = Yii::t('app', 'Reports');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="report-form">
    <div class="row">
        <div class="col-xs-12">
            <h2><?=Yii::t('app', 'Reports');?></h2>
            <hr />
            <br />
            <p><?= Html::a('Отчет отправок по магазинам (отчет без товаров / с товарами)', ['report/report-by-query']) ?></p>
            <p><?= Html::a('Отчет по отклонению реальной стоимости доставки', ['report/deviation-cost']) ?></p>
        </div>
    </div>
</div>

