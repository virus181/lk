<?php

use app\delivery\DeliveryHelper;
use app\models\Courier;
use app\models\Provider;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use app\widgets\Html;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\CourierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Registries');
$this->params['breadcrumbs'][] = $this->title;
Pjax::begin([
    'linkSelector' => false,
]);
?>
<div class="registry-index">
    <div class="page-top page-top-sticky">
        <div class="pull-left">
            <div class="btn-group btn-group-sm">
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
                'columns' => (new \app\models\search\CourierSearch())->getExportColumns($searchModel),
            ]) ?>
        </div>
    </div>
    <?php if(false):?>
    <div class="sub-top">
        <?php
            echo '<i class="fa fa-flip-horizontal fa-level-down"></i>';
            echo Html::button(Yii::t('app', 'Call the courier'), [
                'id' => 'courier-call',
                'class' => 'btn btn-primary btn-sxs',
                'data-toggle' => 'tooltip-helper',
                'title' => 'Выберите заказы для вызова курьера'
            ]);
        ?>
        <?php
        $this->registerJs(<<<JS
            $('[data-toggle="tooltip-helper"]').tooltip({
                'placement': 'right',
                'container': 'body',
                'delay': 0,
            });
JS
        );
        ?>
    </div>
    <?php endif;?>
    <?=
        GridView::widget([
            'tableOptions' => [
                'data-resizable-columns-id' => 'courier',
                'class' => 'table'
            ],
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => (new \app\models\search\CourierSearch())->getSearchColumns($searchModel),
        ]);
    ?>
</div>
<?php Pjax::end(); ?>
<div id="modal-courier-orders" class="fade modal" role="dialog" tabindex="-1">
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
<div id="ready-for-delivery-orders" class="fade modal" role="dialog" tabindex="-1">
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
<?php
$carrierOrdersUrl = Url::to(['order/get-courier-orders']);
$carrierOrderListUrl = Url::to(['courier/order-list']);
$carrierOrdersAddUrl = Url::to(['courier/add-orders']);
$this->registerJs(<<<JS

        $('body').on('click', '.add-orders', function () {
            var courierId = $(this).attr('data-courier-id');
            $.ajax({
                'url': '$carrierOrdersAddUrl',
                data: $('#ready-for-delivery-orders [name="selection[]"]').serialize() + '&id=' + courierId,
                dataType: 'json',
                type: 'POST',
                success: function(data) {
                    window.location.href = data.url;
                },
                error: function(data) {
                    $('.modal-body').html('<h2 class="text-center">'+data.responseJSON.message+'</h2><br/>');
                    console.log(data);
                }
            });
        });

        $('body').on('click', '.add-registry-orders', function () {
          $('#modal-courier-orders').modal('hide');
          $('#ready-for-delivery-orders').modal('show');
          var courierId = $(this).attr('data-courier-id');
          $.ajax({
                'url': '$carrierOrderListUrl',
                data: {courierId : courierId},
                dataType: 'html',
                type: 'POST',
                success: function(data) {
                    var modal = $('#ready-for-delivery-orders'),
                        page = $(data),
                        header = page.find('h1').text(),
                        title = page.find('title').text();
                    
                    page.find('h1').remove();
                    modal.find('.modal-title').remove();
                    modal.find('.modal-header').append('<h5 class="modal-title lead">'+header+'</h5>');
                    modal.find('.modal-body').html(page);
                    
                    if (page.find('title').length > 0) {
                        $('head').find('title').html(title);
                        page.find('title').remove();
                    }
                },
                error: function(data) {
                    $('.modal-body').html('<h2 class="text-center">'+data.responseText+'</h2><br/>');
                    console.log(data);
                }
            });
        });

        $('body').on('click', '.courier-orders', function() {
            var courierId = $(this).attr('data-courier-id');
            $('#modal-courier-orders').modal('show');
            $.ajax({
                'url': '$carrierOrdersUrl',
                data: {courierId : courierId},
                dataType: 'html',
                type: 'POST',
                success: function(data) {
                    var modal = $('#modal-courier-orders'),
                        page = $(data),
                        header = page.find('h1').text(),
                        title = page.find('title').text();
                    
                    page.find('h1').remove();
                    modal.find('.modal-title').remove();
                    modal.find('.modal-header').append('<h5 class="modal-title lead">'+header+'</h5>');
                    modal.find('.modal-body').html(page);
                    
                    if (page.find('title').length > 0) {
                        $('head').find('title').html(title);
                        page.find('title').remove();
                    }
                },
                error: function(data) {
                    $('.modal-body').html('<h2 class="text-center">'+data.responseText+'</h2><br/>');
                    console.log(data);
                }
            });
        });
JS
);

?>