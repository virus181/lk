<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\MaskedInput;
use yii\widgets\Pjax;

?>
<div class="send-message">
    <title>Отправить сообщение</title>
    <h1>Отправить сообщение</h1>
    <div class="row">
        <?php Pjax::begin([
            'id' => 'message-form',
            'formSelector' => '#message-form form',
            'enablePushState' => false,
        ]); ?>
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
            <div class="col-sm-12">
                <div class="form-group">
                    <?= $form->field($model, 'title')->textInput() ?>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <?= $form->field($model, 'fio')->textInput() ?>
                    </div>
                    <div class="col-xs-6">
                        <?= $form->field($model, 'phone', [
                            'template' => "{label}\n<div class=\"input-group\">{input}\n<span class=\"input-group-btn\"><button class=\"btn btn-sm btn-primary\" type=\"button\"><i class='glyphicon glyphicon-earphone'></i></button></span></div>",
                        ])->widget(
                            MaskedInput::className(),
                            [
                                'mask' => '+7 (999) 999-99-99',
                                'options' => [
                                    'class' => 'form-control input-sm',
                                    'placeholder' => '+7 (___) ___-__-__'
                                ]
                            ]
                        ) ?>

                    </div>
                </div>
                <div class="form-group">
                    <div class="select_wrapper_sm select_block relative">
                        <?= $form->field($model, 'type')->dropDownList(\app\models\Message::getMessageTypes(), ['class' => 'form-control input-sm']) ?>
                    </div>
                </div>
                <div class="form-group">
                    <?= $form->field($model, 'text')->textarea() ?>
                </div>
                <div class="form-group">
                    <?= $form->field($model, 'file')->fileInput() ?>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group text-right mt15 mb0">
                    <?= Html::submitButton(
                            Yii::t('app', 'Send'),
                            ['class' => 'btn btn-success', 'id' => 'call-courier-button']
                    ) ?>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
        <?php Pjax::end(); ?>
    </div>
</div>
