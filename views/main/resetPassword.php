<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\forms\ResetPasswordForm */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$this->title = Yii::t('app', 'Reset password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reset-password">
    <div class="logo">
        <img src="logo.svg"/>
    </div>
    <?php
    $form = ActiveForm::begin([
        'id' => 'reset-password',
        'layout' => 'horizontal',
        'enableClientValidation' => false,
        'errorCssClass' => false,
        'fieldConfig' => [
            'template' => "<div class=\"col-sm-12\">{input}</div>\n",
            'labelOptions' => ['class' => 'col-lg-3 control-label'],
        ],
    ]);
    ?>
    <?php
    if ($model->hasErrors()) {
        $errors = [];
        foreach ($model->errors as $errorsField) {
            foreach ($errorsField as $error) {
                $errors[] = $error;
            }
        }
        echo Html::tag('div', implode('<br/>', $errors), ['class' => 'error-summary']);
    }
    ?>
    <p><?= Yii::t('app', 'Please choose your new password:') ?></p>
    <?= $form->field($model, 'password')->passwordInput(['autofocus' => true, 'placeholder' => $model->getAttributeLabel('password')]) ?>
    <div class="form-group">
        <div class="col-sm-12">
            <?= Html::submitButton(Yii::t('app', 'Save new password'), ['class' => 'btn btn-success', 'name' => 'login-button']) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div>