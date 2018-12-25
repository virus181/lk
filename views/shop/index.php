<?php

use app\models\Shop;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\ShopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Shops');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="shop-index">
    <?php Pjax::begin(); ?>
    <div class="page-top page-top-sticky">
        <div class="pull-left">
            <?=
            Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create Shop'), Url::to(['create']), [
                'class' => 'btn btn-warning btn-sm'
            ]);
            ?>
        </div>
        <div class="right">
            <?= ExcelExport::widget([
                'dataProvider' => $exportProvider,
                'columns' => (new \app\models\search\ShopSearch())->getExportColumns($searchModel),
            ]) ?>
        </div>
    </div>
        <?=
            GridView::widget([
                'tableOptions' => [
                    'data-resizable-columns-id' => 'shop',
                    'class' => 'table'
                ],
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'rowOptions' => function (Shop $shop) {
                    return [
                        'data-href' => Url::to(['view', 'id' => $shop->id]),
                        'data-toggle' => 'modal',
                        'data-target' => '#modal',
                    ];
                },
                'columns' => (new \app\models\search\ShopSearch())->getSearchColumns($searchModel),
            ]);
        ?>
    <?php Pjax::end(); ?>
</div>
