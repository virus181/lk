<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $shop app\models\Shop */
/* @var $warehouses app\models\Warehouse[] */

$this->title = Yii::t('app', 'Update shop');
?>
<div class="account-update">
    <title><?= Html::encode($this->title) ?></title>
    <h1><?= Html::encode(Yii::t('app', 'Shop: {name}', ['name' => $shop->name])) ?></h1>
    <?= $this->render('_view', [
        'shop' => $shop,
        'isActive' => $isActive,
        'role' => $role
    ]) ?>
</div>
