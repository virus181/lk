<?php

use app\widgets\Alert;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $shop \app\models\Shop */
/* @var $shopTariff \app\models\ShopTariff */
/* @var $managers array */

$this->title = Yii::t('app', 'Shop tariff edit');
?>
<div class="tariff-wrapper">

    <h1><?php echo Yii::t('shop', 'Shop tariff edit'); ?></h1>

    <?php Pjax::begin([
        'id'              => 'shop-tariff-form',
        'formSelector'    => '#shop-tariff-form form',
        'enablePushState' => false
    ]); ?>

    <?= Alert::widget(['options' => ['style' => 'margin-bottom:20px']]) ?>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-xs-8">
            <?=
            $form->field($shop, 'managerIds')->widget(Select2::className(), [
                'data'          => $managers,
                'options'       => [
                    'multiple' => true
                ],
                'showToggleAll' => false,
                'pluginOptions' => [
                    'closeOnSelect' => false,
                ],
            ]);
            ?>
        </div>
        <div class="col-xs-4">
            <div class="select_wrapper_sm with_label relative">
                <label class="control-label"
                       for="shop-tariffname"><?= Yii::t('shop', 'Tariff name'); ?></label>
                <?= Html::activeDropDownList(
                    $shopTariff,
                    'code',
                    $shop->getTariffNames(),
                    ['class' => 'form-control']
                ) ?>
                <?= Html::activeInput('hidden', $shopTariff, "id") ?>
            </div>
        </div>
        <div class="col-xs-8">
            <div class="select_wrapper_sm with_label relative">
                <label class="control-label"
                       for="shop-tariffname"><?= Yii::t('manager', 'Work time'); ?></label>
                <?= Html::activeDropDownList(
                    $shopTariff,
                    'work_time',
                    $shop->getWorkSchemes(),
                    ['class' => 'form-control']
                ) ?>
            </div>
        </div>

        <div class="col-xs-4">
            <label class="control-label"><?= Yii::t('shop', 'Work scheme url'); ?></label>
            <?= Html::activeInput(
                'text',
                $shopOption,
                'work_scheme_url',
                ['class' => 'form-control']
            ) ?>
        </div>
        <div class="col-xs-12 checkbox-form">
            <?= Html::activeCheckbox(
                $shopTariff,
                'as_vip',
                ['class' => 'form-control', 'label' => false]
            ) ?>
            <label for="shoptariff-as_vip" class="control-label inline">Работа по графику тарифа VIP</label>
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
<?php
Yii::$app->view->registerJs(<<<JS
    
    function getWortTimes(code) {
        if ($('[name="ShopTariff[as_vip]"]').is(':checked')) {
            code = '';
        }
        var currentValue = $('[name="ShopTariff[work_time]"]').val();
        $.ajax({
            type: 'GET',
            url: 'get-worktime-by-tariff-code',
            data: {tariffCode: code},
            dataType: "json",
            success: function(responseData) {
                var options = '';
                $.each(responseData, function(k, v) {
                    if (k == currentValue) {
                        options += '<option selected="selected" value="' + k + '">' + v + '</option>';
                    } else {
                        options += '<option value="' + k + '">' + v + '</option>';
                    }
                    
                });
                $('[name="ShopTariff[work_time]"]').html(options);
            }
        });
    }

    $('[name="ShopTariff[code]"]').change(function() {
        var code = $(this).val();
        getWortTimes(code);
    });

    $('[name="ShopTariff[as_vip]"]').change(function() {
        getWortTimes('');
    });

    var code = $('[name="ShopTariff[code]"]').val();
    getWortTimes(code);
    
JS
);
?>
