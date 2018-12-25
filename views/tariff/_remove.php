<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $shop app\models\Shop */
/* @var $tariff app\models\Tariff */
/* @var $warehouses app\models\Warehouse[] */
/* @var $carrierKeys array */
/* @var $deliveryMethods array */

$this->title = Yii::t('app', 'Remove tariff');
?>

<?php Pjax::begin([
    'id' => 'tariff-form',
    'formSelector' => '#product-form form',
    'enablePushState' => false,
]); ?>
<?php $form = ActiveForm::begin(); ?>
<div class="tariff-form">
    <h2 class="text-center">Вы дейстивтельно хотите удалить тариф?</h2>

    <?= $form->field($tariff, 'shop_id')->hiddenInput()->label(false) ?>
    <?= $form->field($tariff, 'id')->hiddenInput()->label(false) ?>

    <div class="form-group text-center mt15">
        <?= Html::submitButton('<i class="fa fa-check"></i> ' . Yii::t('app', 'Delete'), ['class' => 'btn btn-warning']) ?>
        <?= Html::button(Yii::t('app', 'Cancel'), [
            'class' => 'btn btn-default',
            'data-dismiss' => 'modal',
        ]) ?>
    </div>

</div>
<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
