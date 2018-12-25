<?php

use app\widgets\Alert;
use app\widgets\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $order app\models\Order */

$this->title = $title;
?>
<div class="order-helper">
    <title><?= Html::encode($this->title) ?></title>
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <?php Pjax::begin(); ?>
        <?php if($status == \app\models\Order::STATUS_IN_COLLECTING || $status == \app\models\Order::STATUS_CONFIRMED):?>
            <?php if($order->shop->fulfillment):?>
                <script type="text/javascript">
                    setTimeout(function () {
                        $('#modal-incollect-order').modal('hide');
                        location.reload();
                    }, 1000)
                </script>
            <?php else:?>
                <div class="col-sm-12">
                    <p class="text-center"><?=$message;?></p>
                </div>
                <div class="col-sm-12">
                    <div class="form-group text-center mt15 mb0">
                        <?= Html::button($button, ['class' => 'btn btn-success', 'id' => 'courier-call']) ?>
                        <?= Html::button(Yii::t('app', 'No, call courier later'), ['class' => 'btn btn-warning', 'data-dismiss' => 'modal']) ?>
                    </div>
                </div>
            <?php endif;?>
        <?php endif;?>
        <?php if($status == \app\models\Order::STATUS_CREATED):?>
            <div class="col-sm-12">
                <p class="text-center"><?=$message;?></p>
            </div>
            <div class="col-sm-12">
                <div class="form-group text-center mt15 mb0">
                    <?= Html::button($button, ['class' => 'btn btn-success', 'id' => 'change-status']) ?>
                    <?= Html::button(Yii::t('app', 'No, send order later'), ['class' => 'btn btn-warning', 'data-dismiss' => 'modal']) ?>
                </div>
            </div>
        <?php endif;?>
        <?php Pjax::end(); ?>
    </div>
</div>
