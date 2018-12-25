<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $warehouse app\models\Warehouse */

$this->title = Yii::t('app', 'Create Warehouse');
?>
<div class="warehouse-create">
    <title><?= Html::encode($this->title) ?></title>
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'warehouse' => $warehouse,
    ]) ?>
</div>
