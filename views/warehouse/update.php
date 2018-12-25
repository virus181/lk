<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $warehouse app\models\Warehouse */

$this->title = Yii::t('warehouse', 'Update warehouse');
$this->params['breadcrumbs'][] = ['label' => Yii::t('warehouse', 'Warehouses'), 'url' => Url::to(['warehouse/index'])];
if ($warehouse->id) {
    $this->params['breadcrumbs'][] = Yii::t('warehouse', 'Warehouse <span class="num">№ {id}</span>', ['id' => $warehouse->id]);
} else {
    $this->params['breadcrumbs'][] = Yii::t('warehouse', 'New warehouse');
}
?>
<div class="warehouse-update">
    <title><?= Html::encode($this->title) ?></title>
    <?= $this->render('_update', [
        'warehouse' => $warehouse,
    ]) ?>
</div>
