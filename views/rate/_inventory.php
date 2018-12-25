<?php
use yii\helpers\Html;

/**
 * @var \app\models\RateInventory $inventory
 */

$inventory->weight_to = $inventory->weight_to ? round($inventory->weight_to / 1000, 3) : $inventory->weight_to;
$inventory->weight_from = $inventory->weight_from ?round($inventory->weight_from / 1000, 3) : $inventory->weight_from;

?>
<div class="row inventory-row" id="group-<?= $i ?>">
    <div class="col-xs-2 weight_from">
        <?= Html::activeInput('text', $inventory, "[$i]weight_from", ['class' => 'form-control input-sm']) ?>
    </div>
    <div class="col-xs-2 weight_to">
        <?= Html::activeInput('text', $inventory, "[$i]weight_to", ['class' => 'form-control input-sm']) ?>
    </div>
    <div class="col-xs-2 price_from">
        <?= Html::activeInput('number', $inventory, "[$i]price_from", ['class' => 'form-control input-sm']) ?>
    </div>
    <div class="col-xs-2 price_to">
        <?= Html::activeInput('number', $inventory, "[$i]price_to", ['class' => 'form-control input-sm']) ?>
    </div>
    <div class="col-xs-2 cost">
        <?= Html::activeInput('number', $inventory, "[$i]cost", ['class' => 'form-control input-sm']) ?>
    </div>
    <div class="col-xs-1">
        <?= Html::activeInput('hidden', $inventory, "[$i]id") ?>
    </div>
    <div class="col-xs-1 action">
        <?php if($inventory->id && $i != $total - 1):?>
            <button class="btn btn-sm btn-default" type="button"><i class="fa fa-trash"></i></button>
        <?php else:?>
            <a href="javascript:void(0)" id="addInventory"
               class="btn btn-sm btn-primary pull-right">
                <i class="fa fa-plus"></i>
            </a>
        <?php endif;?>

    </div>
</div>