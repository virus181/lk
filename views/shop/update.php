<?php

use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $shop app\models\Shop */
/* @var $warehouses app\models\Warehouse[] */

$this->title = Yii::t('app', 'Update shop');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Shops'), 'url' => Url::to(['shop/index'])];
if ($shop->id) {
    $this->params['breadcrumbs'][] = Yii::t('app', 'Shop <span class="num">№ {id}</span>', ['id' => $shop->id]);
} else {
    $this->params['breadcrumbs'][] = Yii::t('app', 'New Shop');
}
?>
<div class="account-update">
    <?= $this->render('_form', [
        'deliveries'         => $deliveries,
        'deliveryTypes'      => $deliveryTypes,
        'isActive'           => $isActive,
        'queryParams'        => $queryParams,
        'rights'             => $rights,
        'roundingItems'      => $roundingItems,
        'roundingItemValues' => $roundingItemValues,
        'shop'               => $shop,
        'tariffs'            => $tariffs,
        'warehouses'         => $warehouses,
    ]) ?>
</div>
