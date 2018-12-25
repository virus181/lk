<?php

use app\delivery\DeliveryHelper;
use app\models\forms\OrdersCourierCall;
use app\models\Helper;
use app\models\Order;
use app\widgets\ActiveForm;
use app\widgets\Alert;
use app\widgets\grid\ModalGridView;
use app\widgets\Html;
use kartik\date\DatePicker;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\widgets\grid\CheckboxColumn;

/** @var OrdersCourierCall $ordersCourierCall */
/** @var Order $order */

$this->title = Yii::t('app', 'The Courier Call');
$columns = [
    [
        'class' => CheckboxColumn::className(),
        'headerOptions' => [
            'width' => '30px',
        ],
    ],
    [
        'attribute' => 'created_at',
        'content' => function (Order $order) {
            return Html::a(date('d.m.Y, H:i', $order->created_at), Url::to(['view', 'id' => $order->id]));
        },
    ],
    [
        'attribute' => 'id',
        'label' => Yii::t('app', 'Fastery number'),
        'content' => function (Order $order) {
            return Html::a($order->id, Url::to(['view', 'id' => $order->id]));
        },
    ],
    [
        'attribute' => 'shop_order_number',
        'label' => Yii::t('app', 'Shop number'),
        'content' => function (Order $order) {
            return Html::a($order->shop_order_number, Url::to(['view', 'id' => $order->id]));
        },
    ],
    [
        'attribute' => 'cost',
        'label' => Yii::t('app', 'Order Amount'),
        'content' => function (Order $model) {
            return Yii::$app->formatter->asCurrency($model->cost, 'RUB');
        },
    ],
    [
        'attribute' => 'fio',
        'label' => Yii::t('app', 'fio'),
        'contentOptions' => [
            'class' => 'frr',
            'style' => 'overflow: hidden',
        ],
    ],
    [
        'attribute' => 'phone',
        'label' => Yii::t('app', 'Phone'),
        'content' => function (Order $model) {
            return '<span style="white-space: nowrap;">' . $model->getNormalizePhone() . '</span>';
        }
    ],
    [
        'attribute' => 'codCost',
        'label' => 'Сумма',
        'content' => function (Order $model) {
            return Yii::$app->formatter->asCurrency($model->getCodCost(), 'RUB');
        }
    ],
    [
        'attribute' => 'shop_id',
        'label' => Yii::t('app', 'Shop'),
        'content' => function (Order $model) {
            return $model->shop->name;
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
        ), ['class' => 'select_wrapper'])
    ]
];
?>
<div class="courier-call">
    <div class="row">
        <?php if (empty($orders)
            && $ordersCourierCall->orderIds != false
            && $ordersCourierCall->carriers != false
        ): ?>
            <?php Pjax::begin([
                'id' => 'courier-call-form',
                'formSelector' => '#courier-call-form form',
                'enablePushState' => false,
            ]); ?>
            <title><?= Html::encode($this->title) ?></title>
            <h1><?= Html::encode($this->title) ?></h1>
            <div class="col-sm-12">
                <?= Alert::widget(['options' => ['style' => 'margin-bottom:20px']]) ?>
            </div>
                <?php $form = ActiveForm::begin(); ?>
                <?php if(isset($dataProvider)
                    && $dataProvider->totalCount
                ):?>
                    <div class="col-sm-12">
                        <h5>У вас так же имеется еще
                            <?=Html::a($dataProvider->totalCount . Helper::getNumEnding(
                                    $dataProvider->totalCount,
                                    [' заказ', ' заказа', ' заказов']
                                ),
                                '#',
                                ['class' => 'show-orders', 'data-pjax' => 0]
                            );?>, в статусе готовы к отгрузке, хотите добавить их в реестр? -
                            <?=Html::a('Да',
                                '#',
                                ['class' => 'show-orders', 'data-pjax' => 0]
                            );?>
                        </h5>
                        <div class="other-orders">
                            <?=
                            ModalGridView::widget([
                                'dataProvider' => $dataProvider,
                                'columns' => $columns,
                            ]);
                            ?>
                        </div>
                    </div>
                <?php endif;?>
                <div class="col-sm-6">
                    <h5 class="num"><?= Yii::t('app', 'Couriers will be called for the following delivery services (warehouse):') ?></h5>
                    <?php foreach ($ordersCourierCall->carriers as $warehouseName => $carriers): ?>
                        <?php foreach ($carriers['carrier_keys'] as $carrierKey => $orders): ?>
                            <div class="num bold"><?= DeliveryHelper::getName($carrierKey) ?>
                                <span data-toggle="tooltip" data-placement="bottom" title="Адрес склада: <?= $carriers['warehouse_address'] ?>" class="tip">(<?= $warehouseName ?>)</span>
                            </div> - <span data-toggle="tooltip" data-placement="bottom" title="Заказы: <?= implode(', ', $orders) ?>" class="num tip"><?= Yii::t('app', '{count} {n, plural, one{order} other{orders}}', ['n' => count($orders), 'count' => count($orders)]) ?></span>
                            <br/>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <?= $form->field($ordersCourierCall, 'orderIds')->dropDownList($ordersCourierCall->orderIds, ['multiple' => true, 'class' => 'hidden'])->label(false) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($ordersCourierCall, 'pickup_date')->widget(DatePicker::className(), [
                        'language' => 'ru',
                        'pickerButton' => false,
                        'removeButton' => false,
                        'type' => DatePicker::TYPE_INPUT,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'pickTime' => false,
                            'format' => 'dd.mm.yyyy',
                            'todayHighlight' => true,
                            'daysOfWeekDisabled' => '0,6',
                            'startDate' => date('d.m.Y', strtotime("+1 days")),
                        ]
                    ]) ?>
                </div>

                <div class="col-sm-12">
                    <div class="form-group text-right mt15 mb0">
                        <?php if(isset($dataProvider) && $dataProvider->totalCount):?>
                            <?= Html::button('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add'), ['class' => 'btn btn-primary', 'id' => 'add-orders-to-call']) ?>
                        <?php endif?>
                        <?= Html::submitButton('<i class="fa fa-truck"></i> ' . Yii::t('app', 'Call'), ['class' => 'btn btn-success', 'id' => 'call-courier-button']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            <?php Pjax::end(); ?>
        <?php elseif (isset($orders) && count($orders)): ?>
            <!-- Если курьеры вызваны и можно распечатать этикетки -->
            <div class="col-sm-12">
                <div class="col-xs-12"><p>Курьер вызван, вы можете распечатать этикетки и реестры сейчас или позже до прибытия курьера.</p></div>
                <div class="col-xs-6">
                    <h3>Этикетки:</h3>
                    <?php foreach($orders as $order):?>
                        <p>
                            <?=Html::a('<i class="fa fa-save"></i> Заказ #' . $order->id,
                                ['label/pdf', 'id' => $order->id],
                                ['target' => '_blank', 'data-pjax' => 0]
                            );?>
                        </p>
                    <?php endforeach;?>
                </div>
                <div class="col-xs-6">
                    <h3>Реестры:</h3>
                    <?php $couriers = [];?>
                    <?php foreach($orders as $order):?>
                        <?php if (!in_array($order->courier->id, $couriers)):?>
                            <p>
                                <?=Html::a('<i class="fa fa-save"></i> Реестр #' . $order->courier->id,
                                    ['courier/download', 'id' => $order->courier->id],
                                    ['target' => '_blank', 'data-pjax' => 0]
                                );?>
                            </p>
                            <?php $couriers[] = $order->courier->id;?>
                        <?php endif;?>
                    <?php endforeach;?>
                </div>
                <div class="col-xs-12">
                    <div class="bg-danger package-requirement">
                        <p>Внимание. Ознакомьтесь с
                            <?=Html::a('требованиями упаковки',
                                ['main/package'],
                                ['target' => '_blank']
                            );?> от службы доставки перед тем, как отправить заказ.
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Если произошла какая то фигня -->
            <div class="col-sm-12">
                <?= Alert::widget(['options' => ['style' => 'margin-bottom:20px']]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
Yii::$app->view->registerJs(<<<JS
    $('body').delegate('#call-courier-button', 'click', function() {
        $('#courier-call-form').prepend('<div class="col-sm-12">' +
         '<div class="alert-danger alert fade in" style="margin-bottom:20px">' +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
            'Внимание! Вызов курьера может занять продолжительное время, убедительная просьба, дождаться завершения процедуры вызова курьера.' +
          '</div>' +
        '</div>');
    });   
JS
);
?>