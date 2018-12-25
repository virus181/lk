<?php

use yii\helpers\Html;
use yii\widgets\MaskedInput;

/**
 * @var \app\models\ShopPhone $phone
 */
?>
<div class="row phone-row" id="group-<?= $i ?>">
    <div class="col-xs-6 phone_number">
        <div class="form-group">
            <?= MaskedInput::widget([
                'name' => 'ShopPhone['.$i.'][phone]',
                'mask' => '+7 (999) 999-9999',
                'id'   => 'shop_phone_' . $i,
                'value' => $phone->phone
            ]); ?>
        </div>
    </div>
    <div class="col-xs-5 provider_code">
        <div class="form-group">
            <div class="select_wrapper_sm relative">
                <?= Html::activeInput('hidden', $phone, "[$i]id") ?>
                <?= Html::activeDropDownList($phone, "[$i]provider_code", $phoneProviders, ['class' => 'form-control']) ?>
            </div>
        </div>
    </div>
    <div class="col-xs-1 action text-right">
        <?php if ($phone->id && $i != $total - 1): ?>
            <button class="btn btn-default" type="button"><i class="fa fa-trash"></i></button>
        <?php else: ?>
            <a href="javascript:void(0)" id="addPhone"
               class="btn btn-primary pull-right">
                <i class="fa fa-plus"></i>
            </a>
        <?php endif; ?>
    </div>
</div>