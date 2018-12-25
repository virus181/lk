<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $shops app\models\Shop[] */

$this->title = Yii::t('app', 'Update User');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => Url::to(['user/index'])];
if ($model->id) {
    $this->params['breadcrumbs'][] = Html::encode(Yii::t('app', 'User: {name}', ['name' => $model->fio]));
} else {
    $this->params['breadcrumbs'][] = Yii::t('app', 'New User');
}
?>

<div class="user-update">
    <?= $this->render('_form', [
        'user' => $model,
        'shops' => $shops,
        'available' => $available,
        'manager' => $manager
    ]) ?>
</div>
