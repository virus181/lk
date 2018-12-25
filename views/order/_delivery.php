<?php

use app\models\OrderDelivery;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $orderDelivery \app\models\OrderDelivery */
/* @var $orderDisabled boolean */
?>
<div class="delivery">
    <div class="row">
        <div class="col-sm-2 col-print-2">
            <div class="img">
                <img src="<?= $orderDelivery->getIconPath() ?>">
            </div>
        </div>
        <div class="col-sm-10 col-print-10">
            <div class="row">
                <div class="col-sm-4 col-print-4">
                    <h2><?= Yii::t('app','Shipment') ?></h2>
                    <div class="row">
                        <div class="col-xs-12">
                            <span>Дата отгрузки</span>
                        </div>
                        <div class="col-xs-6 date-from">
                            <?= DatePicker::widget([
                                'language' => 'ru',
                                'pickerButton' => false,
                                'removeButton' => false,
                                'name' => 'OrderDelivery[pickup_date]',
                                'value' => $orderDelivery->getPickupDateFormat(),
                                'disabled' => $orderDisabled,
                                'type' => DatePicker::TYPE_INPUT,
                                'options' => [
                                    'class' => 'form-control input-sm',
                                    'id' => 'pickup_date_picker'
                                ],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'pickTime' => false,
                                    'format' => 'dd.mm.yyyy',
                                    'todayHighlight' => true,
                                    'daysOfWeekDisabled' => '0,6',
                                    'startDate' => $orderDelivery->getPickupDateFormat(),
                                ],
                                'pluginEvents' => [
                                     'changeDate' => "function(e) {
                                                        window.pickupDateChanged(e);
                                                    }"
                                ]
                            ]) ?>
                        </div>
                    </div>
                    <div class="clearfix">
                        <span>Способ отгрузки</span>
                        <p>Забор курьером</p>
                    </div>
                </div>
                <div class="col-sm-4 col-print-4 border-left border-right">
                    <h2><?= Yii::t('app', 'Delivery') ?></h2>
                    <div class="row">
                        <div class="col-xs-12">
                            <span>Расчетная дата доставки</span>
                        </div>
                        <div class="col-xs-6 date-from">
                            <?= DatePicker::widget([
                                'language' => 'ru',
                                'pickerButton' => false,
                                'removeButton' => false,
                                'name' => 'OrderDelivery[delivery_date]',
                                'value' => $orderDelivery->getDeliveryDateFormat(),
                                'disabled' => $orderDisabled,
                                'type' => DatePicker::TYPE_INPUT,
                                'options' => [
                                    'class' => 'form-control input-sm',
                                    'id' => 'delivery_date_picker'
                                ],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'pickTime' => false,
                                    'format' => 'dd.mm.yyyy',
                                    'todayHighlight' => true,
                                    'startDate' => date('d.m.Y', strtotime("+" . $orderDelivery->min_term . " days", $orderDelivery->pickup_date)),
                                ],
                                'pluginEvents' => [
                                    'changeDate' => "function(e) {
                                                        window.deliveryDateChanged(e);
                                                    }"
                                ]
                            ]) ?>
                        </div>
                        <div class="col-xs-3 no-padding-left">
                            <div class="form-group">
                                <?= MaskedInput::widget([
                                    'name'          => 'OrderDelivery[time_start]',
                                    'value'         => $orderDelivery->time_start,
                                    'options'       => [
                                        'class'       => 'form-control input-sm time-input',
                                        'placeholder' => '--:--',
                                        'disabled'    => $orderDisabled,
                                    ],
                                    'clientOptions' => [
                                        'alias'       => 'hh:mm',
                                        'placeholder' => '--:--',
                                    ]
                                ]); ?>
                            </div>
                        </div>
                        <div class="col-xs-3 no-padding-left">
                            <div class="form-group">
                                <?= MaskedInput::widget([
                                    'name'          => 'OrderDelivery[time_end]',
                                    'value'         => $orderDelivery->time_end,
                                    'options'       => [
                                        'class'       => 'form-control input-sm time-input',
                                        'placeholder' => '--:--',
                                        'disabled'    => $orderDisabled,
                                    ],
                                    'clientOptions' => [
                                        'alias'       => 'hh:mm',
                                        'placeholder' => '--:--',
                                    ]
                                ]); ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span>Способ доставки</span>
                        <p class="mb0"><?= $orderDelivery->getDeliveryTypeName() ?> <?= $orderDelivery->getDeliveryServiceNames() ?></p>
                        <?php if ($orderDelivery->type === OrderDelivery::DELIVERY_TO_POINT): ?>
                            <p class="mt0"><?= $orderDelivery->point_address ?></p>
                        <?php endif; ?>
                    </div>

                </div>
                <div class="col-sm-4 col-print-4">
                    <h2><?= Yii::t('app', 'Cost') ?></h2>
                    <div>
                        <span>Стоимость доставки</span>
                        <p><?= Yii::t('app', '{0} rub', [$orderDelivery->original_cost]) ?></p>
                    </div>
                    <?php if ($orderDisabled === false): ?>
                        <div>
                            <?= Html::button(Yii::t('app', 'Change delivery'), ['id' => 'change-delivery', 'class' => 'btn btn-sm btn-primary pull-right']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?= Html::activeInput('hidden', $orderDelivery, 'uid', ['disabled' => $orderDelivery->disabledEdit]) ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'pickup_date', ['disabled' => $orderDelivery->disabledEdit]) ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'tariff_id', ['disabled' => $orderDelivery->disabledEdit]) ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'type', ['disabled' => $orderDelivery->disabledEdit]) ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'partial', ['disabled' => $orderDelivery->disabledEdit]) ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'min_term', ['disabled' => $orderDelivery->disabledEdit]) ?>
</div>
