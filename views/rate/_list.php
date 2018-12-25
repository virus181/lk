<?php

use app\models\Rate;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var Rate[] rates */

$this->title = Yii::t('app', 'Update rate');
?>
<div class="rate-wrapper">
    <?php
    /** @var Rate $rate */
    foreach ($rates as $rate) {
        echo "<div class='row'>";
        echo "<div class='col-xs-9'>";
        if ($rate->name) echo $rate->name;
        else {
            if ($rate->type == \app\models\OrderDelivery::DELIVERY_TO_POINT) {
                echo 'ПВЗ: ' . $rate->getPVZName();
            } else {
                echo 'Курьером: ' . $rate->shop->defaultWarehouse->address->city . " - " . $rate->getCityName();
            }
        }
        echo "</div>";
        ?>
        <div class="col-xs-3">
            <?= Html::a('<i class="fa fa-trash"></i>', null, [
                'data-rate-id' => $rate->id,
                'class' => 'btn btn-sm btn-default pull-right remove-rate'
            ]);
            ?>
            <?= Html::a('<i class="fa fa-pencil"></i>', ['#'], [
                'data-href' => Url::to(['rate/add', 'shopId' => $rate->shop_id, 'rateId' => $rate->id]),
                'data-toggle' => 'modal',
                'data-target' => '#modal',
                'class' => 'btn btn-sm btn-default pull-right'
            ]);
            ?>
        </div>
        <?php
        echo "</div>";
    }
    ?>
</div>