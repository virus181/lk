<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user \app\models\User */
?>
<div class="password-reset">
    <p>Hello <?= Html::encode($user->fio) ?>,</p>

    <p>Your new access below.</p>

    <p>Signup link: <?= Html::a(Html::encode(Url::to(['main/login'], true)), Url::to(['main/login'], true)) ?></p>
    <p>Login: <?= $user->email ?></p>
    <p>Password: <?= $user->password ?></p>
</div>