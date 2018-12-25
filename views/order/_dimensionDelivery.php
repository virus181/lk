<?php

use app\models\OrderDelivery;
use yii\helpers\Html;
use kartik\date\DatePicker;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $orderDelivery \app\models\OrderDelivery */
/* @var $orderDisabled boolean */
?>
<div class="delivery dimensions">
    <div class="row">
        <div class="col-sm-3 col-print-3">
            <div class="img">
                <img src="<?= $orderDelivery->getIconPath() ?>">
            </div>
        </div>
        <div class="col-sm-9 col-print-9">
            <div class="row">
                <div class="col-sm-4 col-print-4 border-left border-right">
                    <div class="row">
                        <div class="col-xs-12">
                            <span>Дата отгрузки</span>
                            <p><?= $orderDelivery->getPickupDateFormat() ?></p>

                        </div>
                        <div class="col-xs-12">
                            <span>Способ отгрузки</span>
                            <p>Забор курьером</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-print-4 border-right">
                    <div class="row">
                        <div class="col-xs-12">
                            <span>Расчетная дата доставки</span>
                            <p><?php echo $orderDelivery->delivery_date
                                    ? date('d.m.Y', $orderDelivery->delivery_date)
                                    : date('d.m.Y', strtotime("+" . $orderDelivery->min_term . " days", $orderDelivery->pickup_date));?></p>
                        </div>
                        <div class="col-xs-12">
                            <span>Способ доставки</span>
                            <p <?php if ($orderDelivery->type === OrderDelivery::DELIVERY_TO_POINT): ?>
                                    data-toggle="tooltip"
                                    data-placement="bottom"
                                    title="<?= $orderDelivery->point_address ?>"
                            <?php endif; ?>>
                                <?= $orderDelivery->getDeliveryTypeName() ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-print-4">
                    <div class="row">
                        <div class="col-xs-12">
                            <span>Стоимость доставки</span>
                            <p><?= Yii::t('app', '{0} rub', [$orderDelivery->original_cost]) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= Html::activeInput('hidden', $orderDelivery, 'uid') ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'pickup_date') ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'tariff_id') ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'type') ?>
    <?= Html::activeInput('hidden', $orderDelivery, 'partial') ?>
</div>
