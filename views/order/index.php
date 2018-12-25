<?php

use app\assets\OrdersAsset;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $exportProvider yii\data\ArrayDataProvider */

OrdersAsset::register($this);

$this->title                   = Yii::t('app', 'Orders');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="order-index">
    <?php
    Pjax::begin([
        'linkSelector' => '.btn-default',
        'timeout'      => 2000,
    ]);
    ?>
    <?php Yii::$app->domParams->getContextValues($queryParams); ?>
    <div class="page-top page-top-sticky">
        <div class="pull-left">
            <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Order'), ['create'], ['class' => 'btn btn-warning btn-sm']) ?>
            <div class="btn-group btn-group-sm ml15">
                <?php foreach ($searchModel->getFilters() as $filter): ?>
                    <?php
                    if (!empty($filter['params']['status']) && $filter['params']['status'] == 'deliveryError' && !$hasDeliveryErrorOrders) {
                        continue;
                    }
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
            <?= Html::a('<i class="fa fa-cog"></i>', '#', [
                'class'       => 'btn btn-default btn-sm',
                'data-href'   => Url::to(['order/columns']),
                'data-toggle' => 'modal',
                'data-target' => '#modal',
            ]) ?>
            <?= ExcelExport::widget([
                'dataProvider' => $exportProvider,
                'columns'      => (new \app\models\search\OrderSearch())->getExportColumns($searchModel),
            ]); ?>
            <?= Html::a(
                '<i class="fa fa-check"></i>',
                '#',
                [
                    'class'          => 'btn btn-success btn-sm confirm-orders-button',
                    'data-toggle'    => "tooltip",
                    'data-placement' => "bottom",
                    'title'          => Yii::t('order', 'Confirm selected orders')
                ]
            ); ?>
            <?= Html::a(
                '<i class="fa fa-archive"></i>',
                '#',
                [
                    'class' => 'btn btn-warning btn-sm',
                    'id' => 'archive-orders',
                    'data-toggle'    => "tooltip",
                    'data-placement' => "bottom",
                    'title'          => Yii::t('order', 'Archiving selected orders')
                ]
            ); ?>
            <?= Html::a(
                '<i class="fa fa-times"></i>',
                '#',
                [
                    'class' => 'btn btn-danger btn-sm',
                    'id' => 'cancel-orders',
                    'data-toggle'    => "tooltip",
                    'data-placement' => "bottom",
                    'title'          => Yii::t('order', 'Cancel selected orders')
                ]
            ); ?>
        </div>
    </div>
    <div class="sub-top">
        <?php
        foreach ($searchModel->getFilters() as $filter) {
            if ($searchModel->isFilterActive($filter) && ($extraButtons = ArrayHelper::getValue($filter, 'extraButtons', []))) {
                echo '<i class="fa fa-flip-horizontal fa-level-down"></i>';
                foreach ($extraButtons as $extraButton) {
                    echo Html::button($extraButton['name'], $extraButton['options']);
                }
            }
        }
        ?>
    </div>
    <?php if (isset($desc)): ?>
        <p><?= $desc ?></p>
    <?php endif; ?>
    <?=
    GridView::widget([
        'tableOptions' => [
            'data-resizable-columns-id' => 'order',
            'class'                     => 'table'
        ],
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => (new \app\models\search\OrderSearch())->getSearchColumns($searchModel)
    ]);
    ?>
    <?php Pjax::end(); ?>
</div>
<div id="modal-courrier-call" class="fade modal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="text-center"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>
            </div>
        </div>
    </div>
</div>
<div id="modal-cancel-order" class="fade modal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <div class="text-center"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>
            </div>
        </div>
    </div>
</div>