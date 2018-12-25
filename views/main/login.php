<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\forms\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="login">
    <div class="logo">
        <img src="logo-white.svg"/>
    </div>
    <?php
        $form = ActiveForm::begin([
            'id' => 'login-form',
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
    <?= $form->field($model, 'password')->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

    <div class="form-group">
        <div class="col-sm-12">
            <?= Html::activeCheckbox($model, 'rememberMe') ?>
            <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-warning', 'name' => 'login-button']) ?>
        </div>
    </div>
    <hr>
    <div class="text-center">
        <?php if (ArrayHelper::getValue(Yii::$app->params, 'demo')): ?>
            <a class="ml15 mt8" href="<?= Url::to(['/main/signup']) ?>"><?= Yii::t('app', 'Signup') ?></a><br><br>
        <?php endif; ?>
        <a class="ml15 mt8" href="<?= Url::to(['/main/forgot-password']) ?>"><?= Yii::t('app', 'Forgot password?') ?></a>
    </div>
    <?php ActiveForm::end(); ?>
</div>
