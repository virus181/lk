<?php

use app\delivery\DeliveryHelper;
use app\models\Order;
use app\models\Provider;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use app\workflow\WorkflowHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Couriers');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="order-index">
    <?php
        Pjax::begin([
            //'linkSelector' => '.btn-default',
        ]);
    ?>
    <?php
    $columns = [
        [
            'class' => CheckboxColumn::className(),
            'headerOptions' => [
                'width' => '40px',
                'data-resizable-column-id' => 'rfd-checker'
            ],
        ],
        [
            'attribute' => 'created_at',
            'headerOptions' => [
                'width' => '200px',
                'data-resizable-column-id' => 'rfd-created-at'
            ],
            'content' => function (Order $order) {
                return Html::a(date('d.m.Y, H:i', $order->created_at), Url::to(['view', 'id' => $order->id]));
            },
            'filter' => Html::tag('div',
                DateRangePicker::widget([
                    'name' => 'created_at',
                    'value' => $searchModel->created_at,
                    'convertFormat'=>false,
                    'useWithAddon'=> false,
                    'options' => ['class' => 'form-control input-xs', 'id' => 'order-create-date'],
                    'pluginOptions'=>[
                        'locale'=>[
                            'format'=>'DD.MM.YYYY',
                            'separator'=>' - ',
                        ]
                    ]
                ])
            ),
        ],
        [
            'attribute' => 'id',
            'label' => Yii::t('app', 'Fastery number'),
            'headerOptions' => [
                'width' => '50px',
                'data-resizable-column-id' => 'rfd-id'
            ],
            'content' => function (Order $order) {
                return Html::a($order->id, Url::to(['view', 'id' => $order->id]));
            },
        ],
        [
            'attribute' => 'shop_order_number',
            'label' => Yii::t('app', 'Shop number'),
            'headerOptions' => [
                'width' => '50px',
                'data-resizable-column-id' => 'rfd-shop-number'
            ],
            'content' => function (Order $order) {
                return Html::a($order->shop_order_number, Url::to(['view', 'id' => $order->id]));
            },
        ],
        [
            'attribute' => 'cost',
            'label' => Yii::t('app', 'Order Amount'),
            'headerOptions' => [
                'width' => '150px',
                'data-resizable-column-id' => 'rfd-order-amount'
            ],
            'content' => function (Order $model) {
                return Html::a($model->getCost(), Url::to(['view', 'id' => $model->id]));
            },
        ],
        [
            'attribute' => 'fio',
            'label' => Yii::t('app', 'fio'),
            'headerOptions' => [
                'width' => '400px',
                'data-resizable-column-id' => 'rfd-fio'
            ],
            'content' => function (Order $model) {
                return Html::a($model->fio, Url::to(['view', 'id' => $model->id]));
            },
            'contentOptions' => [
                'class' => 'frr',
                'style' => 'overflow: hidden',
            ],
        ],
        [
            'attribute' => 'phone',
            'label' => Yii::t('app', 'Phone'),
            'content' => function (Order $model) {
                return Html::a($model->getNormalizePhone(), Url::to(['view', 'id' => $model->id]), ['style' => 'white-space: nowrap;']);
            },
            'headerOptions' => [
                'width' => '250px',
                'data-resizable-column-id' => 'rfd-phone'
            ],
        ],
        [
            'attribute' => 'carrier_key',
            'label' => Yii::t('app', 'SD'),
            'content' => function (Order $order) {
                if ($order->delivery) {
                    return Html::a(DeliveryHelper::getName($order->delivery->carrier_key), Url::to(['view', 'id' => $order->id]));
                } else {
                    return '';
                }
            },
            'headerOptions' => [
                'width' => '50px',
                'data-resizable-column-id' => 'rfd-sd'
            ],
            'contentOptions' => [
                'class' => 'frr',
                'style' => 'overflow: hidden',
            ],
            'filterOptions' => [
                'style' => 'position: relative;',
            ],
            'filter' => Html::tag('div', Html::dropDownList(
                'carrier_key',
                $searchModel->carrier_key,
                ArrayHelper::merge(['' => ''], Provider::getProviders()),
                [
                    'class' => 'form-control input-xs' . (($searchModel->carrier_key === null || $searchModel->carrier_key === '') ? '' : ' selected'),
                ]
            ), ['class' => 'select_wrapper']),
        ],
        [
            'attribute' => 'codCost',
            'label' => Yii::t('app', 'COD'),
            'content' => function (Order $model) {
                return Html::a($model->getCod(), Url::to(['view', 'id' => $model->id]));
            },
            'headerOptions' => [
                'width' => '50px',
                'data-resizable-column-id' => 'rfd-cod'
            ],
        ],
        [
            'attribute' => 'shop_id',
            'label' => Yii::t('app', 'Shop'),
            'content' => function (Order $model) {
                return Html::a($model->shop->name, Url::to(['view', 'id' => $model->id]));

            },
            'filterOptions' => [
                'style' => 'position: relative;',
            ],
            'filter' => Html::tag('div', Html::dropDownList(
                'shop_id',
                $searchModel->shop_id,
                array_replace([null => ''], Yii::$app->user->identity->getAllowedShops()),
                [
                    'class' => 'form-control input-xs' . (($searchModel->shop_id === null || $searchModel->shop_id === '') ? '' : ' selected'),
                ]
            ), ['class' => 'select_wrapper']),
            'headerOptions' => [
                'width' => '300px',
                'data-resizable-column-id' => 'rfd-shop-id'
            ],
        ]
    ];
    ?>
    <div class="page-top">
        <div class="btn-group btn-group-sm">
            <?php
            foreach ($searchModel->getFilters() as $filter) {
                if ($filter['params']['status'] == 'redyForDelivery' && ($extraButtons = ArrayHelper::getValue($filter, 'extraButtons', []))) {
                    foreach ($extraButtons as $extraButton) {
                        echo Html::button($extraButton['name'], $extraButton['options']);
                    }
                }
            }
            if ($isShowCreateRegistryButton) {
                echo Html::button(Yii::t('app', 'Create Registry'), [
                    'class' => 'btn btn-default btn-sm',
                    'id' => 'courier-create',
                ]);
            }
            ?>
        </div>
    </div>
    <?php if (isset($desc)): ?>
        <p><?= $desc ?></p>
    <?php endif; ?>
        <?=
            GridView::widget([
                'tableOptions' => [
                    'data-resizable-columns-id' => 'courier-call',
                    'class' => 'table'
                ],
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $columns
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
<div id="modal-courier-create" class="fade modal" role="dialog" tabindex="-1">
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
<?php
$carrierCallUrl = Url::to(['order/courier-call']);
$carrierCreateUrl = Url::to(['courier/create']);
$cancelOrderUrl = Url::to(['order/set-cancel-status-to-orders']);
$this->registerJs(<<<JS
        $('body').on('click', '#courier-call', function() {
            if ($('[name="selection[]"]:checked').length > 0) {
                $('#modal-courrier-call').modal('show');
                $.ajax({
                    'url': '$carrierCallUrl',
                    data: $('[name="selection[]"]').serialize(),
                    dataType: 'html',
                    type: 'POST',
                    success: function(data) {
                        var modal = $('#modal-courrier-call'),
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
                        $('#modal-courrier-call .modal-body').html('<h2 class="text-center">'+data.responseText+'</h2><br/>');
                        console.log(data);
                    }
                });
            } else {
                alert('Выберите хотя бы один заказ из списка');
            }
        });
        $('body').on('click', '#courier-create', function() {
           
                $('#modal-courier-create').modal('show');
                $.ajax({
                    'url': '$carrierCreateUrl',
                    dataType: 'html',
                    type: 'POST',
                    success: function(data) {
                        var modal = $('#modal-courier-create'),
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
                        $('#modal-courier-create .modal-body').html('<h2 class="text-center">'+data.responseText+'</h2><br/>');
                        console.log(data);
                    }
                });
        });

        $('body').on('click', '#cancel-orders', function() {
           if ($('[name="selection[]"]:checked').length > 0) {
               if(confirm('Вы действительно хотите отменить выбранные заказы?')) {
                   $.ajax({
                    'url': '$cancelOrderUrl',
                    data: $('[name="selection[]"]').serialize(),
                    dataType: 'json',
                    type: 'POST',
                    success: function(data) {
                        window.location.href = data.url;
                    },
                    error: function(data) {
                        $('#modal-cancel-order').modal('show');
                        $('#modal-cancel-order .modal-body').html('<h2 class="text-center">'+data.responseJSON.message+'</h2><br/>');
                        console.log(data);
                    }
                });
               }
           } else {
               alert('Выберите хотя бы один заказ из списка');
           }
        });
JS
);

?>
