<?php

use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('registry', 'Registry');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="shop-index">
    <?php Pjax::begin(); ?>
    <div class="page-top page-top-sticky">
        <div class="pull-left">
            <div class="btn-group btn-group-sm">
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
    </div>

        <?=
            GridView::widget([
                'tableOptions' => [
                    'data-resizable-columns-id' => 'registry',
                    'class' => 'table'
                ],
                'rowOptions' => function ($model) {
                    return [
                        'onclick' => 'location.href="'
                            . Url::to(['account/view', 'id' => $model->id]) .'";'
                    ];
                },
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => (new \app\models\search\InvoiceSearch())->getSearchColumns($searchModel),
            ]);
        ?>
    <?php Pjax::end(); ?>
</div>
