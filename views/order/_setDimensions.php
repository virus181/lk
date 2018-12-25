<?php

use app\widgets\Html;
use yii\bootstrap\Progress;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

\app\assets\DimensionAsset::register($this);

/* @var $order app\models\Order */

$this->title = $title;
?>
<title><?php echo $this->title; ?></title>
<div class="dimension">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-xs-12">
            <?php $form = ActiveForm::begin(); ?>
            <div class="row dimension-form">
                <div class="col-sm-3">
                    <?= $form->field($order, 'weight')->textInput([
                        'type' => 'number',
                        'min'  => 0
                    ]) ?>
                </div>
                <div class="col-sm-3">
                    <?= $form->field($order, 'width')->textInput([
                        'type' => 'number',
                        'min'  => 0
                    ]) ?>
                </div>
                <div class="col-sm-3">
                    <?= $form->field($order, 'length')->textInput([
                        'type' => 'number',
                        'min'  => 0
                    ]) ?>
                </div>
                <div class="col-sm-3">
                    <?= $form->field($order, 'height')->textInput([
                        'type' => 'number',
                        'min'  => 0
                    ]) ?>
                </div>
            </div>
            <div id="order-dimension-delivery"></div>
            <div id="calculate-for-dimension" style="display: none;">
                <p class="info-block lead text-muted text-center">
                    <?= Yii::t('app', 'Обратите внимание, 
                    что стоимость доставки может отличаться, после заполнения
                    габаритов и веса заказа'); ?>
                </p>
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
            <div id="deliveries-for-dimensions"></div>
            <div class="row mt15">
                <div class="col-sm-6">
                    <div class="form-group text-left">
                        <?= Html::button(Yii::t('order', 'Update dimensions'), [
                            'class' => 'btn btn-success',
                            'id'    => 'update-dimensions'
                        ]) ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group text-right">
                        <?= Html::button(Yii::t('order', 'No, I want continue'),
                            [
                                'class'       => 'btn btn-warning',
                                'id'          => 'set-next-status',
                                'data-method' => 'POST',
                                'href'        => Url::to(['order/set-status', 'id' => $order->id, 'status' => $order::STATUS_READY_FOR_DELIVERY])
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>