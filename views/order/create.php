<?php
use app\assets\OrderAsset;
use app\models\Order;
use app\models\OrderDelivery;
use app\widgets\ActiveForm;
use app\widgets\Html;
use kartik\switchinput\SwitchInput;
use yii\bootstrap\Progress;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use yii\widgets\Pjax;

OrderAsset::register($this);

/* @var $this yii\web\View */
/* @var $order app\models\Order */
/* @var $showOrderHelper bool */
/* @var $shops app\models\Shop[] */
/* @var $warehouses app\models\Warehouse[] */
/* @var $orderProducts app\models\OrderProduct[] */
/* @var $deliveries \app\models\Orderdelivery[] */
/* @var $message \app\models\OrderMessage */
/* @var $isActiveShowLog boolean */
/* @var $isCurrentStatusShow boolean */

$this->title = Yii::t('app', 'Order');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Orders'), 'url' => Url::to(['order/index'])];
if ($order->id) {
    $this->params['breadcrumbs'][] = Yii::t('app', 'Order <span class="num">№ {id}</span>', ['id' => $order->id]);
} else {
    $this->params['breadcrumbs'][] = Yii::t('app', 'New Order');
}
?>
    <div class="order-create">
<?php Pjax::begin([
    'linkSelector' => false,
    'timeout'      => 3000,
]); ?>
<?php $form = ActiveForm::begin([
    'id'                     => 'order-form',
    'enableClientValidation' => false,
    'fieldConfig'            => [
        'inputOptions' => [
            'class' => 'form-control input-sm',
        ],
        'template'     => "{label}\n{input}\n{hint}",
    ],
]); ?>
<?php Yii::$app->domParams->getContextValues($context); ?>
    <div class="page-top order hidden-print" style="position: relative;">
        <?= Html::submitButton(
            '<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'),
            [
                'class'    => 'btn btn-sm btn-warning',
                'id'       => 'smb',
                'disabled' => $order->disabledEdit
            ]) ?>
        <?php if (!empty($buttons['printLabel'])): ?>
            <a href="<?= Url::to(['label/pdf', 'id' => $order->id]) ?>" class="btn btn-sm btn-default"
               style="left: 260px;"><?= Yii::t('app', 'Get order label') ?></a>
        <?php endif; ?>
        <div class="btn-group" style="left: 395px;">
            <?php if (!empty($buttons['deliveryStatus'])): ?>
                <?=
                Html::button(Yii::t('app', 'Get status history'), [
                    'class'       => 'btn btn-default btn-sm',
                    'data-href'   => Url::to(['order/status-history', 'id' => $order->id]),
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                ]);
                ?>
            <?php endif; ?>
            <?php if (!empty($buttons['log'])): ?>
                <?=
                Html::button(Yii::t('app', 'Get order log info'), [
                    'class'       => 'btn btn-default btn-sm',
                    'data-href'   => Url::to(['log/index', 'owner_id' => $order->id]),
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                ]);
                ?>
            <?php endif; ?>

            <?php if (!empty($buttons['actualStatus'])): ?>
                <?=
                Html::button(Yii::t('app', 'Actual status'), [
                    'class'       => 'btn btn-default btn-sm',
                    'data-href'   => Url::to(['order/get-current-status', 'orderId' => $order->id]),
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                ]);
                ?>
            <?php endif; ?>
        </div>
        <?php if (!empty($buttons['status'])): ?>
            <a href="<?= Url::to(['order/copy', 'id' => $order->id]) ?>" class="btn btn-sm btn-default"
               style="left: 120px;"><?= Yii::t('app', 'Copy order') ?></a>
            <div class="workflow">
                <div class="btn-group">
                    <?php foreach ($order->getStatusButtons() as $button) {
                        echo Html::button($button['title'], $button['options']);
                    } ?>
                    <?php
                    $this->registerJs(<<<JS
                        $('[data-toggle="tooltip-status"]').tooltip({
                            'placement': 'top',
                            'container': 'body',
                            'delay': 0,
                        });
JS
                    );
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="order-form">
        <div class="col-sm-8 col-print-12">
            <div class="row">
                <div class="col-sm-12">
                    <div class="block row">
                        <div class="col-sm-6 col-print-6">
                            <h2><?= Html::encode($this->title) ?> <?= ($order->id) ? Yii::t('app', 'from <span>{date}</span>', ['date' => date('d.m.Y', $order->created_at)]) : '' ?></h2>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= $form->field($order, 'shop_order_number')->textInput(['disabled' => $order->disabledEdit]) ?>
                                    </div>
                                    <div class="form-group">
                                        <div class="select_wrapper_sm select_block relative">
                                            <?= $form->field($order, 'email')->textInput(['disabled' => $order->disabledEdit]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-print-6">
                            <h2><?= Yii::t('app', 'Customer') ?></h2>
                            <div class="row">
                                <div class="col-sm-12">
                                    <?= $form->field($order, 'fio')->textInput(['disabled' => $order->disabledEdit]) ?>
                                    <?= $form->field($order, 'phone', [
                                        'template' => "{label}\n<div class=\"input-group\">{input}\n<span class=\"input-group-btn\"><a class=\"btn btn-sm btn-primary " . ($context['isAvailableCall'] ? 'can-call' : '') . "\"><i class='glyphicon glyphicon-earphone'></i></a></span></div>",
                                    ])->widget(
                                        MaskedInput::className(),
                                        [
                                            'mask'          => '+7 (999) 999-99-99',
                                            'clientOptions' => ['onincomplete' => 'function(){$("#order-phone").removeAttr("value").attr("value","");}'],
                                            'options'       => [
                                                'class'       => 'form-control input-sm',
                                                'placeholder' => '+7 (___) ___-__-__',
                                                'disabled'    => $order->disabledEdit
                                            ]
                                        ]) ?>
                                    <?= $form->field($order, 'comment')->textarea(['class' => 'hidden', 'disabled' => $order->disabledEdit])->label(false); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="form-group">
                                <div class="select_wrapper_sm select_block relative">
                                    <?= $form->field($order, 'shop_id')
                                        ->dropDownList(
                                            $shops,
                                            ['class' => 'form-control input-sm', 'disabled' => $order->disabledEdit]
                                        );
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="block row">
                        <div class="col-sm-12">
                            <h2><?= Yii::t('app', 'Products') ?></h2>
                            <div class=" warehouse-form">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="select_wrapper_sm select_block relative">
                                            <?= $form
                                                ->field($order, 'warehouse_id')
                                                ->dropDownList(
                                                    $warehouses,
                                                    ['class' => 'form-control input-sm', 'disabled' => $order->disabledEdit]
                                                );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="clearfix"></div>
                            <hr/>
                            <div class="product-form form-group">
                                <div class="row">
                                    <div class="col-sm-2 col-print-2">
                                        <label><?= Yii::t('app', 'Barcode') ?></label>
                                    </div>
                                    <div class="col-sm-5 col-print-5">
                                        <label><?= Yii::t('app', 'Name') ?></label>
                                    </div>
                                    <div class="col-sm-1 col-print-1">
                                        <label><?= Yii::t('app', 'Weight') ?></label>
                                    </div>
                                    <div class="col-sm-1 col-print-1">
                                        <label><?= Yii::t('app', 'Price') ?></label>
                                    </div>
                                    <div class="col-sm-1 col-print-1">
                                        <label><?= Yii::t('app', 'Qty') ?></label>
                                    </div>
                                    <div class="col-sm-2 col-print-2">
                                        <label><?= Yii::t('app', 'Accessed Price') ?>&nbsp;<span
                                                    class="fa fa-info-circle"
                                                    data-toggle="tooltip"
                                                    data-placement="bottom"
                                                    title="<?php echo Yii::t('product', 'Estimated cost affects the amount of insurance of goods in the order'); ?>"
                                            ></span></label>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="products">
                                    <?php foreach ($order->orderProducts as $i => $orderProduct) : ?>
                                        <?= $this->render('_product', [
                                            'orderProduct' => $orderProduct,
                                            'disabledEdit' => $order->disabledProductEdit,
                                            'i'            => $i,
                                        ]) ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-sm-12">
                            <hr/>
                        </div>
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-2 dimension-item">
                                    <p class="caption">Габариты заказа:</p>
                                </div>
                                <div class="col-sm-2 dimension-item">
                                    <?= $form->field($order, 'width')->textInput(['disabled' => $order->disabledEdit]) ?>
                                </div>
                                <div class="col-sm-2 dimension-item">
                                    <?= $form->field($order, 'length')->textInput(['disabled' => $order->disabledEdit]) ?>
                                </div>
                                <div class="col-sm-2 dimension-item">
                                    <?= $form->field($order, 'height')->textInput(['disabled' => $order->disabledEdit]) ?>
                                </div>
                                <div class="col-sm-4 product-buttons">
                                    <?php if (!$order->disabledProductEdit): ?>
                                        <a href="javascript:void(0)" id="addProductsByExcel"
                                           class="btn btn-sm btn-primary pull-right"
                                           data-toggle="tooltip"
                                           data-placement="bottom"
                                           title="<?php echo Yii::t('file', 'Load excel file for add products'); ?>"
                                        >
                                            <i class="fa fa-file-excel-o"></i>
                                            &nbsp;<?= Yii::t('order', 'Load products from Excel') ?>
                                        </a>
                                        <a href="javascript:void(0)" id="addProduct"
                                           class="btn btn-sm btn-primary pull-right">
                                            <i class="fa fa-plus"></i>
                                            <?= Yii::t('app', 'add product') ?>
                                        </a>
                                        <div class="hidden"><?= Html::fileInput('products-upload') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <hr/>
                        </div>
                        <div class="col-sm-9 col-print-9">
                            <div class="total row">
                                <div class="col-sm-4  col-print-4">
                                    <label><?= Yii::t('app', 'Count: <div class="sum-quantity">0</div> th.') ?></label>
                                </div>
                                <div class="col-sm-4  col-print-4">
                                    <label><?= Yii::t('app', 'Weight: <div class="sum-weight">0</div> kg.') ?></label>
                                </div>
                                <div class="col-sm-4 col-print-4">
                                    <label><?= Yii::t('app', 'Summ: <div class="sum-price">0</div> rub') ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="block row">
                        <div class="col-sm-12">
                            <h2><?= Yii::t('app', 'Delivery') ?></h2>
                            <div class="row">
                                <?php
                                $afterSelect = <<<JS
                                    if (oldCity !== suggestion.data.city_with_type) {
                                        window.recalcTotal(true);
                                    }
JS
                                ?>
                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="col-xs-8">
                                            <?= $form->field($order->address, 'full_address')
                                                ->textInput([
                                                    'id'       => 'full_address',
                                                    'disabled' => $order->disabledEdit
                                                ]); ?>
                                        </div>
                                        <div class="col-xs-4">
                                            <div class="swtich detailed-address">
                                                <?= $form
                                                    ->field($order, 'address_detailed')
                                                    ->widget(SwitchInput::classname(), [
                                                        'pluginOptions' => [
                                                            'size'    => 'mini',
                                                            'onText'  => 'Вкл',
                                                            'offText' => 'Выкл',
                                                        ],
                                                        'inlineLabel'   => true,
                                                        'labelOptions'  => ['style' => 'font-size: 12px'],
                                                        'options'       => [
                                                            'data-toggle'   => "collapse",
                                                            'data-target'   => "#collapseExample",
                                                            'aria-expanded' => "false",
                                                            'aria-controls' => "collapseExample"
                                                        ],
                                                        'pluginEvents'  => [
                                                            "switchChange.bootstrapSwitch" => "function() { $('#collapse-address').collapse('toggle'); }"
                                                        ]
                                                    ])->label(false); ?>
                                                <label>Адрес детально</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row collapse" id="collapse-address">
                                        <div class="col-xs-4">
                                            <?= $form->field($order->address, 'region')
                                                ->textInput([
                                                    'id'       => 'address-region',
                                                    'class'    => 'form-control input-sm detailed-address-input',
                                                    'disabled' => $order->disabledEdit
                                                ]); ?>
                                        </div>
                                        <div class="col-xs-4">
                                            <?= $form->field($order->address, 'city')
                                                ->textInput([
                                                    'id'       => 'address-city',
                                                    'class'    => 'form-control input-sm detailed-address-input',
                                                    'disabled' => $order->disabledEdit
                                                ]); ?>
                                        </div>
                                        <div class="col-xs-4"><?= $form->field($order->address, 'postcode')->textInput([
                                                'class'    => 'form-control input-sm detailed-address-input',
                                                'disabled' => $order->disabledEdit
                                            ]); ?></div>
                                        <div class="col-xs-12">
                                            <?= $form->field($order->address, 'street')
                                                ->textInput([
                                                    'id'       => 'address-street',
                                                    'disabled' => $order->disabledEdit
                                                ]); ?>
                                        </div>
                                        <div class="col-xs-4"><?= $form->field($order->address, 'house')->textInput([
                                                'class'    => 'form-control input-sm detailed-address-input',
                                                'disabled' => $order->disabledEdit
                                            ]); ?></div>
                                        <div class="col-xs-4"><?= $form->field($order->address, 'flat')->textInput([
                                                'class'    => 'form-control input-sm detailed-address-input',
                                                'disabled' => $order->disabledEdit
                                            ]); ?></div>
                                        <div class="col-xs-4"><?= $form->field($order->address, 'housing')->textInput([
                                                'class'    => 'form-control input-sm detailed-address-input',
                                                'disabled' => $order->disabledEdit
                                            ]); ?></div>
                                        <?= $form->field($order->address, 'id')->hiddenInput()->label(false); ?>
                                        <?= $form->field($order->address, 'region_fias_id')->hiddenInput()->label(false); ?>
                                        <?= $form->field($order->address, 'city_fias_id')->hiddenInput()->label(false); ?>
                                        <?= $form->field($order->address, 'street_fias_id')->hiddenInput()->label(false); ?>
                                        <?= $form->field($order->address, 'address_object')->hiddenInput()->label(false); ?>
                                    </div>
                                    <hr/>
                                </div>
                            </div>
                            <div id="delivery">
                                <div id="order-delivery">
                                    <?php if ($order->delivery !== null && $order->delivery->provider !== null): ?>
                                        <?= $this->render('_delivery', [
                                            'orderDelivery' => $order->delivery,
                                            'orderDisabled' => $order->disabledEdit
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($order->disabledEdit === false): ?>
                                    <p class="alert-block lead text-muted text-center">
                                        <?= Yii::t('app', 'Add products and enter the address of the buyer') ?>
                                    </p>
                                    <div id="calculate" style="display: none;">
                                        <p class="info-block lead text-muted text-center"><?= Yii::t('app', 'Load delivery data') ?></p>
                                        <?php
                                        echo Progress::widget([
                                            'id'         => 'progress-calculate-delivery',
                                            'percent'    => 100,
                                            'barOptions' => [
                                                'class' => 'progress-bar-warning progress-bar-striped active',
                                            ],
                                            'label'      => '<i class="fa fa-truck"></i> ' . Yii::t('app', '... loading ...'),
                                        ]);
                                        ?>
                                    </div>
                                    <div id="deliveries"
                                         class="<?php if ($order->delivery !== null && $order->delivery->provider !== null) : ?>hidden<?php endif; ?>"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="block row">
                        <div class="col-sm-12">
                            <h2><?= Yii::t('app', 'Payment') ?></h2>
                            <div class="form-group">
                                <div class="select_wrapper_sm with_label relative">
                                    <?= $form
                                        ->field($order, 'payment_method')
                                        ->dropDownList(
                                            $order->getPaymentMethods(),
                                            [
                                                'class'       => 'form-control input-sm',
                                                'placeholder' => Yii::t('app', 'COD Cost'),
                                                'disabled'    => $order->disabledEdit
                                            ]
                                        ); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?= Yii::t('order', 'Buyer cost') ?></label>
                                <?= Html::activeInput('text', $order->delivery ?? new OrderDelivery(), 'cost', ['class' => 'form-control input-sm', 'disabled' => $order->disabledEdit]) ?>
                            </div>
                        </div>
                        <div class="col-sm-12 col-print-12">
                            <div id="order-total" class="total row">
                                <div class="col-sm-3  col-print-3">
                                    <label><?= Yii::t('app', 'Products Cost:') ?>&nbsp;
                                        <div class="products-price"><?php echo Yii::$app->formatter->asCurrency($order->getCost(false), 'RUB'); ?></div>
                                    </label>
                                </div>
                                <div class="col-sm-3  col-print-3">
                                    <label><?= Yii::t('app', 'Accessed Cost:') ?>&nbsp;<div
                                                class="accessed-price"><?php echo Yii::$app->formatter->asCurrency($order->getAssessed_cost(), 'RUB'); ?></div>
                                    </label>
                                </div>
                                <div class="col-sm-3  col-print-3">
                                    <label><?= Yii::t('app', 'Delivery Cost:') ?>&nbsp;<div
                                                class="devlivery-price"><?php echo Yii::$app->formatter->asCurrency($order->delivery ? $order->delivery->cost : 0, 'RUB'); ?></div>
                                    </label>
                                </div>
                                <div class="col-sm-3 col-print-3">
                                    <label><?= Yii::t('app', 'Cod Cost:') ?>&nbsp;<div
                                                class="cod-price"><?php echo Yii::$app->formatter->asCurrency($order->getCodCost(), 'RUB'); ?></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4 col-print-12" id="order-left-block">
            <div class="block row">
                <div class="col-sm-12 delivery-info">
                    <h2><?= Yii::t('app', 'Delivery') ?><i class="fa fa-truck"></i></h2>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Статус заказа</label>
                        <p>
                            <i class="fa fa-check-circle"></i>
                            <span
                                <?php if (!empty($button['problemStatus'])): ?>
                                    data-toggle="tooltip"
                                    data-placement="bottom"
                                    title="<?= $order->delivery_status['name']; ?>: <?= $order->delivery_status['description']; ?>"
                                <?php endif; ?>
                            >
                                <?= Yii::t('app', $order->statusName ? $order->statusName : 'New Order') ?></span>
                        </p>
                    </div>
                    <?php if (!empty($order->delivery_status)): ?>
                        <div class="form-group">
                            <label class="control-label" for="order-shop_id">Дата статуса</label>
                            <p><?= date('d.m.Y \в H:i', strtotime($order->delivery_status['created'])); ?></p>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Стоимость доставки</label>
                        <p><?= isset($order->delivery) ? $order->getDeliveryCost(true) : '---'; ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Анонсированные сроки доставки</label>
                        <p><?= isset($order->delivery) ? $order->delivery->getDeliveryTerms() : '---'; ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Ориентировочная дата доставки</label>
                        <p><?= isset($order->delivery) ? $order->delivery->getDeliveryDateFormat() : '---'; ?></p>
                    </div>
                    <?php if ($order->courier_id): ?>
                        <div class="form-group">
                            <label class="control-label" for="order-shop_id">Номер реестра</label>
                            <p><?= $order->courier_id; ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($order->dispatch_number): ?>
                        <div class="form-group">
                            <label class="control-label" for="order-shop_id">Трек номер заказа</label>
                            <p><?= $order->dispatch_number; ?></p>
                        </div>
                    <?php endif; ?>
                    <hr>
                </div>
                <div class="col-sm-12 delivery-info">
                    <h2>Финансы<i class="fa fa-database"></i></h2>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Стоимость товаров</label>
                        <p><?= $order->getCost()
                                ? Yii::$app->formatter->asCurrency($order->getCost(false), 'RUB')
                                : '---'; ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Расчетная стоимость доставки</label>
                        <p><?= isset($order->delivery)
                                ? Yii::$app->formatter->asCurrency($order->delivery->original_cost, 'RUB')
                                : '---';
                            ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Общая сумма заказа</label>
                        <p><?= isset($order->delivery)
                                ? Yii::$app->formatter->asCurrency(($order->getCost(false) + $order->delivery->original_cost), 'RUB')
                                : '---'; ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Объявленная ценность</label>
                        <p><?= $order->getCost(false)
                                ? Yii::$app->formatter->asCurrency($order->getCost(false), 'RUB')
                                : '---';
                            ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Сумма наложенного платежа</label>
                        <p><?= (!is_null($order->getCodCost()) && $order->id)
                                ? Yii::$app->formatter->asCurrency($order->getCodCost(), 'RUB')
                                : '---';
                            ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Фактическая стоимость доставки</label>
                        <p><?= (!empty($actualDeliveryCost))
                                ? Yii::$app->formatter->asCurrency($actualDeliveryCost, 'RUB')
                                : '---';
                            ?></p>
                    </div>
                    <hr>
                </div>
                <div class="col-sm-12 delivery-info">
                    <h2>Веса<i class="fa fa-balance-scale"></i></h2>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Заявленный вес</label>
                        <p><?= $order->getWeight() ? (str_replace(',', '.', $order->getWeight() / 1000) . ' кг.') : '---'; ?></p>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="order-shop_id">Фактический вес</label>
                        <p><?= ($order->getRealWeight())
                                ? (str_replace(',', '.', number_format($order->getRealWeight(), 3, '.', '')) . ' кг.')
                                : '---';
                            ?></p>
                    </div>
                </div>
            </div>
            <?php if ($order->id): ?>
                <?= $this->render('_comments', [
                    'order'    => $order,
                    'messages' => isset($messages) ? $messages : null
                ]) ?>
            <?php endif; ?>
        </div>
        <?php ActiveForm::end(); ?>
        <?php Pjax::end(); ?>
    </div>
    <div id="modal-incollect-order" class="fade modal" role="dialog" tabindex="-1">
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
$onTerminalType = OrderDelivery::PICKUP_TYPE_ON_TERMINAL;

Yii::$app->view->registerJs(<<<JS
    $(body).on('change', '#orderdelivery-pickup_type', function() {
        if ($(this).val() == "$onTerminalType") {
            $('#pickup_terminal').removeClass('hidden');
        } else {
            $('#pickup_terminal').addClass('hidden');
        }
    });
JS
);

// Только для созданных заказов
if ($order->id) {
    // Если да, то покажем всплывающее окно с предложение отправить на сборку заказ
    if (isset($showOrderHelper) && in_array($showOrderHelper, [Order::STATUS_CREATED, Order::STATUS_CONFIRMED])) {

        Yii::$app->view->registerJs(<<<JS
        
        var modalId = '#modal-incollect-order';
        var form = $('#order-form').serialize();
        
        $.ajax({
            url: 'in-collecting-window',
            data: {orderId : '$order->id'},
            dataType: 'json',
            type: 'GET',
            success: [
                function(data) {              
                    var modal = $(modalId),
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
                    $(modalId).modal('show');
                }
            ],
            error: [
                function(data) {
                    console.log(data);
                }
            ]
        });
JS
        );
    }

    $carrierCallUrl         = Url::to(['order/courier-call']);
    $statusReadyForDelivery = \app\models\Order::STATUS_READY_FOR_DELIVERY;
    $statusCollecting       = $order->shop->fulfillment
        ? \app\models\Order::STATUS_IN_COLLECTING
        : \app\models\Order::STATUS_CONFIRMED;

    Yii::$app->view->registerJs(<<<JS
    
        var modalId = '#modal-incollect-order';
        var modalBody = $(modalId).find('.modal-body');
       
        // При закрытии модального окна перезагрузим страницу
        $(modalId).on('hidden.bs.modal', function () {
          location.reload();
        });

        $(document).on('click', '#change-status', function() {
            $.ajax({
                url: 'in-collecting-window',
                data: {orderId : '$order->id', status: '$statusCollecting'},
                dataType: 'json',
                type: 'GET',
                success: [
                    function(data) {              
                        var modal = $(modalId),
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
                    }
                ],
                error: [
                    function(data) {
                        console.log(data);
                    }
                ]
            });
        });
    
        $(document).on('click', '.show-orders', function(e) {
            $('.other-orders').addClass('active');
            $('#add-orders-to-call').addClass('active');
            e.preventDefault();
        });

        $(document).on('click', '#add-orders-to-call', function() {
             var orderIds = $('[name="selection[]"]').serialize() + '&selection%5B%5D=' + '$order->id';
             $.ajax({
                'url': '$carrierCallUrl',
                data: orderIds,
                dataType: 'html',
                type: 'POST',
                success: [
                    function(data) {
                        var modal = $(modalId),
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
                    }
                ],
                error: [
                    function(data) {
                        modalBody.html('<h2 class="text-center">'+data.responseText+'</h2><br/>');
                        console.log(data);
                    }
                ]
            });
        });

        $(document).on('click', '#courier-call', function() {
            $.ajax({
                url: 'in-collecting-window',
                data: {orderId : '$order->id', status: '$statusReadyForDelivery'},
                dataType: 'json',
                type: 'GET',
                success: [
                    function() {
                        $.ajax({
                            'url': '$carrierCallUrl',
                            data: {selection: ['$order->id']},
                            dataType: 'html',
                            type: 'POST',
                            success: [
                                function(data) {
                                    var modal = $(modalId),
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
                                }
                            ],
                            error: [
                                function(data) {
                                    modalBody.html('<h2 class="text-center">'+data.responseText+'</h2><br/>');
                                    console.log(data);
                                }
                            ]
                        });
                    }
                ],
                error: [
                    function(data) {
                        modalBody.html('<h2 class="text-center">'+data.responseText+'</h2><br/>');
                        console.log(data);
                    }
                ]
            });
        });
JS
    );
}
