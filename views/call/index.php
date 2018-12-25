<?php

use app\delivery\DeliveryHelper;
use app\models\Order;
use app\models\Provider;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use app\widgets\Html;
use app\workflow\WorkflowHelper;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\LabelSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

\app\assets\CallsAsset::register($this);

$this->title = Yii::t('app', 'Calls');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="call-index">
    <?php Pjax::begin([
        'linkSelector' => '.btn-default',
    ]); ?>
    <?php Yii::$app->domParams->getContextValues($context);?>
    <div class="page-top page-top-sticky">
        <div class="left">
            <div class="phones"></div>
        </div>
        <div class="right">
            <?= ExcelExport::widget([
                'dataProvider' => $exportProvider,
                'columns' => (new \app\models\search\CallSearch())->getExportColumns($searchModel),
            ]) ?>
        </div>
    </div>
    <?=
    GridView::widget([
        'tableOptions' => [
            'data-resizable-columns-id' => 'call',
            'class' => 'table'
        ],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => (new \app\models\search\CallSearch())->getSearchColumns($searchModel),
    ]);
    ?>
    <?php Pjax::end(); ?>
</div>
