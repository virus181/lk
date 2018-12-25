<?php

use app\delivery\DeliveryHelper;
use app\models\Order;
use app\models\OrderDelivery;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $orderDeliveries OrderDelivery[] */
/* @var $chosenDelivery OrderDelivery */
/* @var $cheapestDelivery OrderDelivery */
/* @var $order Order */
/* @var $disabledEdit boolean */
/* @var $isPartial boolean */
/* @var $filter string */

?>
<?php if ($chosenDelivery): ?>
    <?php if ($order->delivery->original_cost != $chosenDelivery->original_cost): ?>
    <p>Стоимость доставки выбранной вами ранее службой
        <?php echo $order->getDeliveryCarrierName();?> - "<?php echo $order->delivery->getDeliveryTypeName();?>"
        с указанными габаритами изменится с <?php echo Yii::$app->formatter->asCurrency($order->delivery->original_cost, 'RUB');?>
        до <?php echo Yii::$app->formatter->asCurrency($chosenDelivery->original_cost, 'RUB');?>

        <?php if ($cheapestDelivery): ?>
            <a href="#" class="warning show-deliveries">
                В данный момент появилась более дешевая СД (<?php echo (new DeliveryHelper())->getName($cheapestDelivery->carrier_key);?> за
                <?php echo Yii::$app->formatter->asCurrency($cheapestDelivery->cost, 'RUB');?>).
            </a>
        <?php endif;?>

    </p>
    <?php endif;?>
    <div id="previous-delivery" class="delivery dimensions">
        <div class="row">
            <div class="col-sm-3 col-print-3">
                <div class="img">
                    <img src="<?= $chosenDelivery->getIconPath() ?>">
                </div>
            </div>
            <div class="col-sm-9 col-print-9">
                <div class="row">
                    <div class="col-sm-4 col-print-4 border-left border-right">
                        <div class="row">
                            <div class="col-xs-12">
                                <span>Дата отгрузки</span>
                                <p><?= $chosenDelivery->getPickupDateFormat() ?></p>

                            </div>
                            <div class="col-xs-12">
                                <span>Способ отгрузки</span>
                                <p>Забор курьером</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-print-4 border-right">
                        <div class="row">
                            <div class="col-xs-12">
                                <span>Расчетная дата доставки</span>
                                <p><?php echo $chosenDelivery->delivery_date
                                        ? date('d.m.Y', $chosenDelivery->delivery_date)
                                        : date('d.m.Y', strtotime("+" . $chosenDelivery->min_term . " days", $chosenDelivery->pickup_date));?></p>
                            </div>
                            <div class="col-xs-12">
                                <span>Способ доставки</span>
                                <p <?php if ($chosenDelivery->type === OrderDelivery::DELIVERY_TO_POINT): ?>
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="<?= $chosenDelivery->point_address ?>"
                                <?php endif; ?>>
                                    <?= $chosenDelivery->getDeliveryTypeName() ?>
                                </p>
                            </div>

                        </div>
                    </div>
                    <div class="col-sm-4 col-print-4">
                        <div class="row">
                            <div class="col-xs-12">
                                <span>Стоимость доставки</span>
                                <p>
                                    <?php if ($order->delivery->original_cost != $chosenDelivery->original_cost): ?>
                                        <s><?php echo Yii::$app->formatter->asCurrency($order->delivery->original_cost, 'RUB');?></s> -
                                    <?php endif;?>
                                    <?php echo Yii::$app->formatter->asCurrency($chosenDelivery->original_cost, 'RUB');?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?= Html::activeInput('hidden', $chosenDelivery, 'uid') ?>
        <?= Html::activeInput('hidden', $chosenDelivery, 'pickup_date') ?>
        <?= Html::activeInput('hidden', $chosenDelivery, 'tariff_id') ?>
        <?= Html::activeInput('hidden', $chosenDelivery, 'type') ?>
        <?= Html::activeInput('hidden', $chosenDelivery, 'partial') ?>
    </div>
<?php endif; ?>
<?php if ($orderDeliveries) : ?>
    <?php if(!$chosenDelivery):?>
        <p class="error-summary">Выбранная Вами ранее служба доставки не доступна, пожалуйста перевыберите СД.</p>
    <?php endif;?>
    <div id="delivery-calculated-deliveries" <?php if($chosenDelivery):?>class="hidden"<?php endif;?>>
        <div class="row ">
            <div class="col-sm-12">
                <label class="control-label"><?=Yii::t('app', 'Alowed methods') ?></label>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <a class="btn btn-sm btn-default btn-primary" data-toggle="tab" href="#panel1"
                           onclick="$(this).parent().find('a').removeClass('btn-primary');$(this).addClass('btn-primary');"><?= Yii::t('app', 'Carrier') ?></a>
                        <a class="btn btn-sm btn-default fix" data-toggle="tab" href="#panel2"
                           onclick="$(this).parent().find('a').removeClass('btn-primary');$(this).addClass('btn-primary');"><?= Yii::t('app', 'Points') ?></a>
                        <a class="btn btn-sm btn-default fix2" data-toggle="tab" href="#panel3"
                           onclick="$(this).parent().find('a').removeClass('btn-primary');$(this).addClass('btn-primary');"><?= Yii::t('app', 'Mail') ?></a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group services pull-left">
                    <label>
                        <input type="checkbox"
                               name="Order[partial]"
                               class="additional-services"
                               value="1"
                            <?php if($disabledEdit):?> disabled="disabled" <?php endif;?>
                            <?php if($isPartial):?> checked="checked" <?php endif;?>
                        >Частичный выкуп <span
                                class="fa fa-info-circle"
                                data-toggle="tooltip"
                                data-placement="bottom"
                                title="Частичный выкуп - Возможность для покупателя выбрать и оплатить только необходимый ему товар в заказе (понравившийся, подошедший по размеру, цвету и т.п.). Оплата обычно происходит во время получения заказа."
                        ></span>
                    </label>
                </div>
                <div class="btn-group filters pull-right" data-toggle="buttons">
                    <label class="btn btn-sm btn-default <?= $filter == 'cheapest' ? 'active' : '' ?>">
                        <input type="radio" name="filter" value="cheapest"
                               autocomplete="off" <?= $filter == 'cheapest' ? 'checked' : '' ?>>Дешевые
                    </label>
                    <label class="btn btn-sm btn-default <?= $filter == 'fastest' ? 'active' : '' ?>">
                        <input type="radio" name="filter" value="fastest"
                               autocomplete="off" <?= $filter == 'fastest' ? 'checked' : '' ?>>Быстрыe
                    </label>
                    <label class="btn btn-sm btn-default <?= $filter == 'all' ? 'active' : '' ?>">
                        <input type="radio" name="filter" value="all"
                               autocomplete="off" <?= $filter == 'all' ? 'checked' : '' ?>>Все
                    </label>
                </div>
            </div>
        </div>
        <h2 class="hidden lead"><?= Yii::t('app', 'Alowed methods') ?></h2>
        <div class="tab-content">
            <div id="panel1" class="tab-pane active">
                <?php foreach ($orderDeliveries as $orderDelivery) : ?>
                    <?php if ($orderDelivery->type === OrderDelivery::DELIVERY_TO_DOOR) : ?>
                        <div class="quote">
                            <div class="img col-sm-2">
                                <img src="<?= $orderDelivery->getIconPath() ?>">
                            </div>
                            <div class="desc col-sm-3">
                                <p class="name num"><?= $orderDelivery->deliveryName ?></p>
                                <span class="subname num"><?= Yii::t('app', 'Carrier') ?></span>
                            </div>
                            <div class="term col-sm-3">
                                <p class="name"><?= $orderDelivery->getDeliveryTerms() ?></p>
                                <span class="subname"><?= Yii::t('app', 'Shipment as {0}', [$orderDelivery->getPickupDateFormat()]) ?></span>
                            </div>
                            <div class="cost col-sm-2">
                                <p class="name"><?= $orderDelivery->cost . Yii::t('app', ' rub') ?></p>
                            </div>
                            <div class="apply col-sm-2">
                                <?= Html::button(Yii::t('app', 'Choose'), [
                                    'class' => 'btn btn-warning apply-dimension-delivery',
                                    'uid' => $orderDelivery->getUid(),
                                    'carrier-key' => $orderDelivery->carrier_key,
                                    'disabled' => $disabledEdit
                                ]) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div id="panel2" class="tab-pane">
                <?php foreach ($orderDeliveries as $orderDelivery) : ?>
                    <?php if ($orderDelivery->type === OrderDelivery::DELIVERY_TO_POINT && $orderDelivery->point_type !== OrderDelivery::POINT_TYPE_MAIL) : ?>
                        <div class="quote">
                            <div class="img col-sm-2">
                                <img src="<?= $orderDelivery->getIconPath() ?>">
                            </div>
                            <div class="desc col-sm-3">
                                <p class="name num"><?= $orderDelivery->point_address ?></p>
                                <span class="subname phone num"><?= $orderDelivery->phone ?></span>
                            </div>
                            <div class="term col-sm-3">
                                <p class="name"><?= $orderDelivery->getDeliveryTerms() ?></p>
                                <span class="subname"><?= Yii::t('app', 'Shipment as {0}', [$orderDelivery->getPickupDateFormat()]) ?></span>
                            </div>
                            <div class="cost col-sm-2">
                                <p class="name"><?= $orderDelivery->cost . Yii::t('app', ' rub') ?></p>
                            </div>
                            <div class="apply col-sm-2">
                                <?= Html::button(Yii::t('app', 'Choose'),
                                    ['class' => 'btn btn-warning apply-dimension-delivery',
                                     'uid' => $orderDelivery->getUid(),
                                     'disabled' => $disabledEdit]
                                ) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div id="panel3" class="tab-pane">
                <?php foreach ($orderDeliveries as $orderDelivery) : ?>
                    <?php if (($orderDelivery->type === OrderDelivery::DELIVERY_TO_POINT && $orderDelivery->point_type === OrderDelivery::POINT_TYPE_MAIL) || $orderDelivery->type === OrderDelivery::DELIVERY_POST) : ?>
                        <div class="quote">
                            <div class="img col-sm-2">
                                <img src="<?= DeliveryHelper::getIconPath('pochta') ?>">
                            </div>
                            <div class="desc col-sm-3">
                                <p class="name num"><?= $orderDelivery->deliveryName ?></p>
                                <span class="subname phone num"><?= $orderDelivery->name ?></span>
                            </div>
                            <div class="term col-sm-3">
                                <p class="name"><?= $orderDelivery->getDeliveryTerms() ?></p>
                                <span class="subname"><?= Yii::t('app', 'Shipment as {0}', [$orderDelivery->getPickupDateFormat()]) ?></span>
                            </div>
                            <div class="cost col-sm-2">
                                <p class="name"><?= $orderDelivery->cost . Yii::t('app', ' rub') ?></p>
                            </div>
                            <div class="apply col-sm-2">
                                <?= Html::button(Yii::t('app', 'Choose'), ['class' => 'btn btn-warning apply-dimension-delivery', 'uid' => $orderDelivery->getUid(), 'disabled' => $disabledEdit]) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>