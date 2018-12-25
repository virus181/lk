<?php

use app\widgets\Alert;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $rate app\models\Rate */
\app\assets\RateAsset::register($this);

$this->title = Yii::t('app', 'Update rate');
?>
<div class="tariff-wrapper">

    <h1>Собственная курьерская служба</h1>

    <?php Pjax::begin([
        'id' => 'rate-form',
        'formSelector' => '#rate-form form',
        'enablePushState' => false
    ]); ?>

    <?= Alert::widget(['options' => ['style' => 'margin-bottom:20px']]) ?>

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($rate, 'shop_id')->hiddenInput()->label(false) ?>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($rate, 'name')->textInput() ?>
        </div>
        <div class="col-sm-4">
            <?= $form
                ->field($rate, 'type')
                ->dropDownList(
                    $deliveryMethods,
                    ['class' => 'form-control']
                ); ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($rate, 'min_term')->textInput() ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($rate, 'max_term')->textInput() ?>
        </div>

    </div>
    <div class="hidden">
        <?= $form->field($rate->address, 'id')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'region')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'region_fias_id')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'city')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'city_fias_id')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'street')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'street_fias_id')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'house')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'flat')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'housing')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'postcode')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'lat')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'lng')->textInput()->label(false); ?>
        <?= $form->field($rate->address, 'address_object')->textInput()->label(false); ?>
    </div>
    <div class="row">
        <div class="col-sm-8">
            <?= $form->field($rate->address, 'full_address')
                ->textInput([
                    'id' => 'full_address',
                    'class' => 'form-control',
                ])->label(Yii::t('rate', 'Full address. You can leave this field blank to display the courier service in all locations.'));
            ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($rate, 'notify_email')->textInput() ?>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-xs-4">
            <label class="control-label"><?=Yii::t('app', 'Order weight');?></label>
        </div>
        <div class="col-xs-4">
            <label class="control-label"><?=Yii::t('app', 'Order price');?></label>
        </div>
        <div class="col-xs-4">
            <label class="control-label"><?=Yii::t('app', 'Delivery cost');?></label>
        </div>
    </div>
    <div class="divider-horizontal"></div>
    <div class="inventories">
        <?php foreach ($rate->inventories as $i => $inventory) : ?>
            <?= $this->render('_inventory', [
                'inventory' => $inventory,
                'i' => $i,
                'total' => count($rate->inventories)
            ]) ?>
        <?php endforeach; ?>
    </div>
    <hr />
    <div class="rate-form">
        <div class="form-group text-center mt15">
            <?= Html::submitButton('<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-warning']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
</div>