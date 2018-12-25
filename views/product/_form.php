<?php

use app\widgets\Alert;
use app\widgets\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
/* @var $shops app\models\Shop[] */
?>
<?php Pjax::begin([
    'id'              => 'product-form',
    'formSelector'    => '#product-form form',
    'enablePushState' => false,
]); ?>
<div class="product-form">

    <?= Alert::widget(['options' => ['style' => 'margin-bottom:20px']]) ?>

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'name')->textInput(['disabled' => $disabled]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'barcode')->textInput(['disabled' => $disabled]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'price')->textInput(['disabled' => $disabled]) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'accessed_price')->textInput(['disabled' => $disabled]) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'weight')->textInput(['disabled' => $disabled]) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'count')->textInput(['disabled' => $disabled]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="select_wrapper_sm with_label relative">
                <?= $form->field($model, 'shop_id')->dropDownList($shops, ['disabled' => $disabled]) ?>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="select_wrapper_sm with_label relative">
                <?= $form->field($model, 'status')->dropDownList(\app\models\Product::getProductStatuses(), ['disabled' => $disabled]) ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'width')->textInput(['disabled' => $disabled]) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'length')->textInput(['disabled' => $disabled]) ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'height')->textInput(['disabled' => $disabled]) ?>
        </div>
        <div class="col-sm-3">
            <div class="select_wrapper_sm with_label relative">
                <?= $form->field($model, 'is_not_reversible')->dropDownList([
                    0 => 'Нет',
                    1 => 'Да',
                ], ['disabled' => $disabled]) ?>
            </div>
        </div>
    </div>
    <?php if (!$disabled): ?>
        <div class="form-group text-center mt15 mb0">
            <?= Html::submitButton('<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-warning']) ?>
        </div>
    <?php endif; ?>
    <?php ActiveForm::end(); ?>
</div>
<?php Pjax::end(); ?>
