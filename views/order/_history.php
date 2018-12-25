<?php
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $order app\models\Order */
/* @var $status app\models\DeliveryStatus */

Pjax::begin([
    'id' => 'account-form',
    'formSelector' => '#account-form form',
    'enablePushState' => false,
]); ?>
<div class="account-form">
    <div class="row">
        <div class="col-sm-12">
            <h1>Заказ <span class="num">№<?=$order->id;?></span></h1>
            <p>Создан: <span class="num"><?=$order->created_at;?></span></p>
            <p>Служба доставки: <?=$order->delivery->getDeliveryName();?></p>
            <p>Адрес доставки: <?=$order->address->full_address;?></p>
        </div>
    </div>
    <br />
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-light">
                <?php foreach ($statuses as $status):?>
                    <tr>
                        <td><?=$status->status_date;?></td>
                        <td>
                            <b><?=$status->status;?></b><br /><?=$status->description;?>
                        </td>
                    </tr>
                <?php endforeach;?>
            </table>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>
