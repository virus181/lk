<?php

use app\widgets\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $role string */
/* @var $shop app\models\Shop */
/* @var $form yii\widgets\ActiveForm */
/* @var $warehouses app\models\Warehouse[] */

?>
<div class="account-form shop-view">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group field-shop-name required">
                <label class="control-label" for="shop-name">Название магазина</label>
                <p><strong><?=$shop->name;?></strong></p>
            </div>
            <div class="form-group field-shop-name required">
                <label class="control-label" for="shop-name">Склад по умолчанию</label>
                <p><strong><?=($shop->defaultWarehouse) ? $shop->defaultWarehouse->name : '---';?></strong></p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group field-shop-name required">
                <label class="control-label" for="shop-name">Юр. лицо</label>
                <p><strong><?=($shop->legal_entity) ? $shop->legal_entity : '---';?></strong></p>
            </div>
            <div class="form-group field-shop-name required">
                <label class="control-label" for="shop-name">Адрес сайта</label>
                <p><strong><a href="<?=$shop->url;?>"><?=$shop->url;?></a></strong></p>
            </div>
        </div>
        <div class="col-sm-12">
            <?php if($role != \app\models\User::ROLE_WATCHER && $isActive):?>
                <div class="form-group text-center mt15 mb0">
                    <?= Html::a('<i class="fa fa-pencil"></i> ' . Yii::t('app', 'Edit'), \yii\helpers\Url::to(['update', 'id' => $shop->id]), ['class' => 'btn btn-warning']) ?>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>
