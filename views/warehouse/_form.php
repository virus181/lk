<?php

use app\widgets\ActiveForm;
use yii\helpers\Html;
use yii\widgets\MaskedInput;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $warehouse app\models\Warehouse */
/* @var $form yii\widgets\ActiveForm */

\app\assets\WarehouseAsset::register($this);
?>
<?php Pjax::begin([
    'id'              => 'warehouse-form',
    'formSelector'    => '#warehouse-form form',
    'enablePushState' => false,
]); ?>
<div class="warehouse-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($warehouse, 'name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($warehouse, 'contact_fio')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($warehouse, 'contact_phone')->widget(
                MaskedInput::className(),
                [
                    'mask'          => '+7 (999) 999-99-99',
                    'clientOptions' => ['onincomplete' => 'function(){$("#order-phone").removeAttr("value").attr("value","");}'],
                    'options'       => ['class' => 'form-control', 'placeholder' => '+7 (___) ___-__-__']
                ]
            ) ?>
        </div>
    </div>

    <div class="hidden">
        <?= $form->field($warehouse->address, 'id')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'region')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'region_fias_id')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'city')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'city_fias_id')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'street')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'street_fias_id')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'house')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'flat')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'housing')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'postcode')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'lat')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'lng')->textInput()->label(false); ?>
        <?= $form->field($warehouse->address, 'address_object')->textInput()->label(false); ?>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($warehouse->address, 'full_address')
                ->textInput([
                    'id'    => 'full_address',
                    'class' => 'form-control'
                ])
            ?>
            <div class="help-block">
                <?php echo Yii::t('warehouse', 'Please note that at the moment, warehouses with an address other than Moscow and the Moscow region are not served.'); ?>
            </div>
        </div>
    </div>
    <div class="form-group text-center mt15 mb0">
        <?= Html::submitButton(
            '<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'),
            ['class' => 'btn btn-warning']
        ) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php Pjax::end(); ?>
