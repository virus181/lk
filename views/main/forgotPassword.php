<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\forms\ForgotPassword */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Request password reset');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="forgot-password">
    <div class="logo">
        <img src="logo-white.svg"/>
    </div>
    <?php
    $form = ActiveForm::begin([
        'id' => 'forgot-password',
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
    <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'placeholder' => $model->getAttributeLabel('email')]) ?>
    <div class="form-group">
        <div class="col-sm-12">
            <?= Html::submitButton(Yii::t('app', 'Send'), ['class' => 'btn btn-warning', 'name' => 'login-button']) ?>
            <a class="rememberlink"
               href="<?= Url::to(['/main/login']) ?>"><?= Yii::t('app', 'Remember password?') ?></a>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div>