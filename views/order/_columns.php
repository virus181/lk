<?php

use app\widgets\Alert;
use app\widgets\AutocompleteDadata;
use kartik\switchinput\SwitchInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $columns array */

$this->title = Yii::t('app', 'Order columns');
?>
<div class="column-wrapper">
    <h1>Отображение данных</h1>
    <?php Pjax::begin([
        'id' => 'column-form',
        'formSelector' => '#column-form form',
        'enablePushState' => false
    ]); ?>

    <?= Alert::widget(['options' => ['style' => 'margin-bottom:20px']]) ?>

    <?php $form = ActiveForm::begin([
    ]); ?>
    <div class="column-form">
        <div class="row">
            <br />
            <br />
            <?php foreach ($columns as $column => $data):?>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="switch">
                            <?= $form
                                ->field($orderSearch, 'userColumns['.$column.']')
                                ->widget(SwitchInput::classname(), [
                                    'pluginOptions' => [
                                        'size' => 'mini',
                                        'onText' => 'Вкл',
                                        'offText' => 'Выкл',
                                    ]
                                ])->label(false); ?>
                            <label><?=Yii::t('app', $data['label']);?></label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach;?>
        </div>
        <div class="form-group text-center mt15">
            <?= Html::submitButton('<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-warning']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
</div>