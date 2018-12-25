<?php

use app\widgets\Alert;
use app\widgets\AutocompleteDadata;
use kartik\switchinput\SwitchInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $shop app\models\Shop */
/* @var $tariff app\models\Tariff */
/* @var $warehouses app\models\Warehouse[] */
/* @var $carrierKeys array */
/* @var $deliveryMethods array */

$this->title = Yii::t('app', 'Update tariff');
?>
<div class="tariff-wrapper">
    <h1><?php echo Yii::t('shop', 'Add personal rates');?></h1>
    <?php Pjax::begin([
        'id' => 'tariff-form',
        'formSelector' => '#tariff-form form',
        'enablePushState' => false
    ]); ?>

    <?= Alert::widget(['options' => ['style' => 'margin-bottom:20px']]) ?>

    <?php $form = ActiveForm::begin(); ?>
    <div class="tariff-form">
        <div class="row">
            <div class="col-sm-4">
                <?= $form
                    ->field($tariff, 'carrier_key')
                    ->dropDownList(
                        $carrierKeys,
                        ['class' => 'form-control']
                    ); ?>
            </div>
            <div class="col-sm-4">
                <?= $form
                    ->field($tariff, 'type')
                    ->dropDownList(
                        $deliveryMethods,
                        ['class' => 'form-control']
                    ); ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($tariff, 'total')->textInput() ?>
            </div>
        </div>
        <div class="row">
            <div class="swtich col-xs-12">
                <?= $form
                    ->field($tariff, 'detailed')
                    ->widget(SwitchInput::classname(), [
                        'pluginOptions' => [
                            'size' => 'mini',
                            'onText' => 'Вкл',
                            'offText' => 'Выкл',
                        ],
                        'inlineLabel' => true,
                        'labelOptions' => [
                            'style' => 'font-size: 12px'
                        ],
                        'options' => [
                            'data-toggle' => "collapse",
                            'data-target' => "#collapseExample",
                            'aria-expanded' => "false",
                            'aria-controls' => "collapseExample"
                        ],
                        'pluginEvents' => [
                            "switchChange.bootstrapSwitch" => "function() { $('#collapseExample').collapse('toggle'); }"
                        ]
                    ])->label(false); ?>
                <label class="control-label"><?php echo Yii::t('shop', 'Details');?></label>
            </div>
        </div>
        <div class="collapse <?=$tariff->detailed ? 'in' : '';?>" id="collapseExample">
            <?= $form->field($tariff, 'shop_id')->hiddenInput()->label(false) ?>
            <div class="row hidden-collapse-block">
                <div class="col-xs-2">
                    <?= $form
                        ->field($tariff, 'additional_sum_type')
                        ->dropDownList(
                            [
                                '' => 'Выберите',
                                'p' => 'Процент',
                                'f' => 'Сумма'
                            ],
                            ['class' => 'form-control']
                        ); ?>
                </div>
                <div class="col-xs-2">
                    <?= $form
                        ->field($tariff, 'additional_sum_prefix')
                        ->dropDownList(
                            [
                                '' => 'Выберите',
                                '+' => 'В большую сторону',
                                '-' => 'В меньшую сторону'
                            ],
                            ['class' => 'form-control']
                        ); ?>
                </div>
                <div class="col-xs-2">
                    <?= $form->field($tariff, 'additional_sum')->textInput(); ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($tariff, 'city')
                        ->widget(
                            AutocompleteDadata::className(),
                            [
                                'bounds' => 'city-settlement',
                                'modelName' => 'Tariff',
                                'options' => [
                                    'class' => 'form-control',
                                    'placeholder' => Yii::t('app', 'address'),
                                ]
                            ]
                        ) ?>
                    <div class="hidden">
                        <?= $form->field($tariff, 'city_fias_id')->textInput()->label(false); ?>
                    </div>
                </div>
                <div class="col-xs-3 form-group">
                    <label>Сумма заказа: </label>
                    <?= $form->field($tariff, 'min_price')->textInput([
                        'placeholder' => 'от'
                    ])->label(false) ?>
                </div>
                <div class="col-xs-3 form-group">
                    <label>&nbsp;</label>
                    <?= $form->field($tariff, 'max_price')->textInput([
                        'placeholder' => 'до'
                    ])->label(false) ?>
                </div>
                <div class="col-xs-3 form-group">
                    <label>Вес заказа: </label>
                    <?= $form->field($tariff, 'min_weight')->textInput([
                        'placeholder' => 'от'
                    ])->label(false) ?>
                </div>
                <div class="col-xs-3 form-group">
                    <label>&nbsp;</label>
                    <?= $form->field($tariff, 'max_weight')->textInput([
                         'placeholder' => 'до'
                    ])->label(false) ?>
                </div>
            </div>
        </div>

        <div class="form-group text-center mt15">
            <?= Html::submitButton('<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-warning']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
</div>