<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $shop app\models\Shop */
/* @var $warehouses app\models\Warehouse[] */

$this->title = Yii::t('app', 'Create Shop');
?>
<div class="shop-create">
    <title><?= Html::encode($this->title) ?></title>
    <?= $this->render('_form', [
        'shop' => $shop,
        'warehouses' => $warehouses,
        'isActive' => $isActive,
        'tariffs' => $tariffs,
        'deliveries' => $deliveries,
        'roundingItems' => $roundingItems,
        'roundingItemValues' => $roundingItemValues,
        'deliveryTypes' => $deliveryTypes,
        'rights' => $rights,
        'queryParams' => $queryParams,
    ]) ?>
</div>
