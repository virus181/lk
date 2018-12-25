<?php

use app\models\Product;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Products');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-index">
    <?php Pjax::begin(); ?>
    <div class="page-top page-top-sticky">
        <div class="pull-left">
            <?=
            Html::button('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Product'), [
                'class' => 'btn btn-warning btn-sm',
                'data-href' => Url::to(['create']),
                'data-toggle' => 'modal',
                'data-target' => '#modal',
            ]);
            ?>
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
                'columns' => (new \app\models\search\ProductSearch())->getExportColumns($searchModel),
            ]) ?>
        </div>
    </div>
        <?=
            GridView::widget([
                'tableOptions' => [
                    'data-resizable-columns-id' => 'product',
                    'class' => 'table'
                ],
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'rowOptions' => function (Product $product) {
                    return [
                        'data-href' => Url::to(['update', 'id' => $product->id]),
                        'data-toggle' => 'modal',
                        'data-target' => '#modal',
                        'class' => ($product->status != Product::STATUS_ACTIVE) ? 'disabled-row' : 'active-row'
                    ];
                },
                'columns' => (new \app\models\search\ProductSearch())->getSearchColumns($searchModel),
            ]);
        ?>
    <?php Pjax::end(); ?>
</div>
