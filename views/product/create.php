<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $shops app\models\Shop[] */

$this->title = Yii::t('app', 'Create Product');
?>
<div class="product-create">
    <title><?= Html::encode($this->title) ?></title>
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'shops' => $shops,
        'disabled' => false
    ]) ?>
</div>
