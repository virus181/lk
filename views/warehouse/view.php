<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $warehouse app\models\Warehouse */

$this->title = Yii::t('app', 'Update warehouse');
?>
<div class="warehouse-update">
    <title><?= Html::encode($this->title) ?></title>
    <h1><?= Html::encode(Yii::t('app', 'Warehouse: {name}', ['name' => $warehouse->name])) ?></h1>
    <div class="warehouse-view">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group field-shop-name required">
                    <label class="control-label" for="shop-name">Название склада</label>
                    <p><strong><?= $warehouse->name; ?></strong></p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group field-shop-name required">
                    <label class="control-label" for="shop-name">Контактое лицо</label>
                    <p><strong><?= $warehouse->contact_fio; ?></strong></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group field-shop-name required">
                    <label class="control-label" for="shop-name">Контактный телефон</label>
                    <p><strong class="num"><?= $warehouse->contact_phone; ?></strong></p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group field-shop-name required">
                    <label class="control-label" for="shop-name">Полный адрес</label>
                    <p><strong><?= $warehouse->address->full_address; ?></strong></p>
                </div>
            </div>
        </div>
        <div class="form-group text-center mt15 mb0">
            <?= Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Edit'), \yii\helpers\Url::to(['update', 'id' => $warehouse->id]), ['class' => 'btn btn-warning']) ?>
        </div>
    </div>
</div>
