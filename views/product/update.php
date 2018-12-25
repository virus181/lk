<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $shops app\models\Shop[] */

$this->title = Yii::t('app', 'Update Product');
?>
<div class="product-update">
    <title><?= Html::encode($this->title) ?></title>
    <h1><?= Html::encode(Yii::t('app', 'Product: {name}', ['name' => $model->name])) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'shops' => $shops,
        'disabled' => $disabled
    ]) ?>
</div>
