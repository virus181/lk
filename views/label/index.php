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

\app\assets\LabelAsset::register($this);

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\LabelSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Labels');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-index">

    <?php Pjax::begin([
        'linkSelector' => false,
    ]); ?>
    <?php Yii::$app->domParams->getContextValues($context); ?>
    <div class="page-top page-top-sticky">
        <div class="pull-left">
            <div>
                <?= Html::a('<i class="fa fa-arrow-down"></i> Скачать', '#', [
                    'class' => 'btn btn-warning btn-sm',
                    'id' => 'download-labels'
                ]) ?>
            </div>
            <div class="btn-group btn-group-sm ml15">
                <?php foreach ($searchModel->getFilters() as $filter): ?>
                    <?php
                    $options = $filter['options'];
                    if ($searchModel->isFilterActive($filter)) {
                        Html::addCssClass($options, 'btn-primary');
                        $desc = ArrayHelper::getValue($filter, 'desc');
                    }
                    ?>
                    <?= Html::a($filter['name'], $searchModel->createFilterUrl($filter), $options) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="right">
            <!-- <?= Html::a('<i class="fa fa-cog"></i>', '#', ['class' => 'btn btn-default btn-sm']) ?> -->
            <?= ExcelExport::widget([
                'dataProvider' => $exportProvider,
                'columns' => (new \app\models\search\LabelSearch())->getExportColumns($searchModel),
            ]) ?>
        </div>
    </div>
    <?=
    GridView::widget([
        'tableOptions' => [
            'data-resizable-columns-id' => 'label',
            'class' => 'table'
        ],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => (new \app\models\search\LabelSearch())->getSearchColumns($searchModel),
    ]);
    ?>
    <?php Pjax::end(); ?>
</div>
