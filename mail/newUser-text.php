<?php
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user \app\models\User */

?>
    Hello <?= $user->fio ?>,

    Your new access below.

    Signup link: <?= Url::to(['main/login'], true) ?>
    Login: <?= $user->email ?>
    Password: <?= $user->password ?>