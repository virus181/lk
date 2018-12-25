<?php

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\Courier */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="courier-call">
    <?php Pjax::begin([
        'id' => 'courier-call-form',
        'formSelector' => '#courier-call-form form',
        'enablePushState' => false,
    ]); ?>
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-sm-4">
            <?= $form
                ->field($model, 'carrier_key')
                ->dropDownList(
                    $carrierKeys,
                    ['class' => 'form-control']
                ); ?>
        </div>

        <div class="col-sm-4">
            <?= $form
                ->field($model, 'warehouse_id')
                ->dropDownList(
                    $warehouses,
                    ['class' => 'form-control']
                );
            ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'pickup_date')->widget(DatePicker::className(), [
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
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
</div>
