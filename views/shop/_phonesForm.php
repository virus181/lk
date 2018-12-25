<?php

use app\widgets\Alert;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $shop \app\models\Shop */
/* @var $shopTariff \app\models\ShopTariff */
/* @var $managers array */

$this->title = Yii::t('app', 'Shop phones edit');
?>
<div class="phones-wrapper">

    <h1><?php echo Yii::t('shop', 'Shop phones edit'); ?></h1>

    <?php Pjax::begin([
        'id'              => 'shop-phones-form',
        'formSelector'    => '#shop-phones-form form',
        'enablePushState' => false
    ]); ?>

    <?= Alert::widget(['options' => ['style' => 'margin-bottom:20px']]) ?>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-6">
                    <label><?= Yii::t('shop', 'Phone'); ?></label>
                </div>
                <div class="col-xs-6">
                    <label><?= Yii::t('shop', 'Provider'); ?></label>
                </div>
            </div>
        </div>
        <div class="col-sm-12 phones">
            <?php foreach ($shop->phones as $i => $phone) : ?>
                <?= $this->render('_phones', [
                    'i'              => $i,
                    'phone'          => $phone,
                    'phoneProviders' => $phoneProviders,
                    'total'          => count($shop->phones)
                ]) ?>
            <?php endforeach; ?>
        </div>

        <div class="col-xs-4">
            <div class="form-group select_wrapper_sm with_label relative">
                <label class="control-label"
                       for="shop-tariffname"><?= Yii::t('manager', 'First queue'); ?></label>
                <?= Html::activeDropDownList(
                    $shopOption,
                    'first_queue',
                    (new \app\models\Manager())->getQueues(),
                    ['disabled' => true, 'class' => 'form-control']
                ) ?>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group select_wrapper_sm with_label relative">
                <label class="control-label"
                       for="shop-tariffname"><?= Yii::t('manager', 'Second queue'); ?></label>
                <?= Html::activeDropDownList(
                    $shopOption,
                    'second_queue',
                    (new \app\models\Manager())->getQueues(),
                    ['class' => 'form-control']
                ) ?>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group select_wrapper_sm with_label relative">
                <label class="control-label"
                       for="shop-tariffname"><?= Yii::t('manager', 'Third queue'); ?></label>
                <?= Html::activeDropDownList(
                    $shopOption,
                    'third_queue',
                    (new \app\models\Manager())->getQueues(),
                    ['class' => 'form-control']
                ) ?>
            </div>
        </div>

        <hr/>

        <div class="rate-form col-xs-12">
            <div class="form-group text-center mt15">
                <?= Html::submitButton(
                        '<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-warning']
                ) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <?php Pjax::end(); ?>
</div>
