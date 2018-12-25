<?php

use app\models\Warehouse;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\WarehouseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Warehouses');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warehouse-index">
    <?php Pjax::begin(); ?>
    <div class="page-top page-top-sticky">
        <div class="pull-left">
            <?=
            Html::button('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Warehouse'), [
                'class' => 'btn btn-warning btn-sm',
                'data-href' => Url::to(['create']),
                'data-toggle' => 'modal',
                'data-target' => '#modal',
            ]);
            ?>
        </div>
        <div class="right">
            <!-- <?= Html::a('<i class="fa fa-cog"></i>', '#', ['class' => 'btn btn-default btn-sm']) ?> -->
            <?= ExcelExport::widget([
                'dataProvider' => $exportProvider,
                'columns' => (new \app\models\search\WarehouseSearch())->getExportColumns($searchModel),
            ]) ?>
        </div>
    </div>
        <?= GridView::widget([
            'tableOptions' => [
                'data-resizable-columns-id' => 'warehouse',
                'class' => 'table'
            ],
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'rowOptions' => function (Warehouse $warehouse) {
                return [
                    'data-href' => Url::to(['view', 'id' => $warehouse->id]),
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                ];
            },
            'columns' => (new \app\models\search\WarehouseSearch())->getSearchColumns($searchModel),
        ]); ?>
    <?php Pjax::end(); ?>
</div>
