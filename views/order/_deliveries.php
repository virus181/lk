<?php

use app\delivery\DeliveryHelper;
use app\models\OrderDelivery;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $orderDeliveries OrderDelivery[] */
/* @var $disabledEdit boolean */
/* @var $isPartial boolean */
/* @var $filter string */

?>
<?php if ($orderDeliveries) : ?>
    <div class="row">
        <div class="col-sm-12">
            <label class="control-label"><?= Yii::t('app', 'Alowed methods') ?></label>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <div class="btn-group btn-group-justified tab-panels" data-toggle="buttons">
                    <a class="btn btn-sm btn-default btn-primary" id="tab-panel-courier" data-toggle="tab" href="#panel1"
                       onclick="$(this).parent().find('a').removeClass('btn-primary');$(this).addClass('btn-primary');">
                        <?= Yii::t('app', 'Carrier') ?>
                    </a>
                    <a class="btn btn-sm btn-default fix" id="tab-panel-point" data-toggle="tab" href="#panel2"
                       onclick="$(this).parent().find('a').removeClass('btn-primary');$(this).addClass('btn-primary');">
                        <?= Yii::t('app', 'Points') ?>
                    </a>
                    <a class="btn btn-sm btn-default fix2" id="tab-panel-mail" data-toggle="tab" href="#panel3"
                       onclick="$(this).parent().find('a').removeClass('btn-primary');$(this).addClass('btn-primary');">
                        <?= Yii::t('app', 'Mail') ?>
                    </a>
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
                        <?php if ($disabledEdit): ?> disabled="disabled" <?php endif; ?>
                        <?php if ($isPartial): ?> checked="checked" <?php endif; ?>
                    >Частичный выкуп <span
                            class="fa fa-info-circle"
                            data-toggle="tooltip"
                            data-placement="bottom"
                            title="Частичный выкуп - Возможность для покупателя выбрать и оплатить только необходимый ему товар в заказе (понравившийся, подошедший по размеру, цвету и т.п.). Оплата обычно происходит во время получения заказа."
                    ></span>
                </label>
            </div>
            <div class="btn-group filters pull-right ml15" data-toggle="buttons">
                <label class="btn btn-sm btn-default" id="shop-on-map">
                    <input type="checkbox" name="map" value="1"
                           autocomplete="off">На карте
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
                                'class'       => 'btn btn-warning apply-delivery',
                                'uid'         => $orderDelivery->getUid(),
                                'data-carrier-key' => $orderDelivery->carrier_key,
                                'tariff-id'   => $orderDelivery->tariff_id,
                                'data-cost'   => $orderDelivery->cost,
                                'disabled'    => $disabledEdit,
                                'type'        => $orderDelivery->type,
                            ]) ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div id="panel2" class="tab-pane">
            <div class="point-map hidden">
                <div id="map"></div>
            </div>
            <div class="point-list">
                <?php foreach ($orderDeliveries as $orderDelivery) : ?>
                    <?php if ($orderDelivery->type === OrderDelivery::DELIVERY_TO_POINT
                        && $orderDelivery->point_type !== OrderDelivery::POINT_TYPE_MAIL
                    ): ?>
                        <div class="quote"
                             data-lng="<?= $orderDelivery->lng ?>"
                             data-lat="<?= $orderDelivery->lat ?>"
                             data-name="<?= $orderDelivery->carrier_key ?>"
                             data-cost="<?= $orderDelivery->cost ?>"
                             data-price="<?= $orderDelivery->cost . Yii::t('app', ' rub') ?>"
                             data-address="<?= $orderDelivery->point_address ?>"
                             data-phone="<?= $orderDelivery->phone ?>"
                             data-description="<?= $orderDelivery->point_id ?>"
                             data-uid="<?= $orderDelivery->getUid() ?>"
                             data-tariff-id="<?= $orderDelivery->tariff_id ?>"
                             data-type="<?= $orderDelivery->type ?>"
                             data-delivery-name="<?= $orderDelivery->getDeliveryName() ?>"
                             data-terms="<?= $orderDelivery->getDeliveryTerms() ?>"
                             data-pickup="<?= $orderDelivery->getPickupDateFormat() ?>"
                             data-carrier-key="<?= $orderDelivery->carrier_key ?>"
                        >
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
                                <?= Html::button(
                                    Yii::t('app', 'Choose'),
                                    [
                                        'class'            => 'btn btn-warning apply-delivery',
                                        'uid'              => $orderDelivery->getUid(),
                                        'data-carrier-key' => $orderDelivery->carrier_key,
                                        'tariff-id'        => $orderDelivery->tariff_id,
                                        'data-cost'        => $orderDelivery->cost,
                                        'disabled'         => $disabledEdit,
                                        'type'             => $orderDelivery->type,
                                    ]
                                ) ?>
                            </div>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
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
                            <?= Html::button(
                                Yii::t('app', 'Choose'),
                                [
                                    'class'       => 'btn btn-warning apply-delivery',
                                    'uid'         => $orderDelivery->getUid(),
                                    'data-carrier-key' => $orderDelivery->carrier_key,
                                    'data-cost' => $orderDelivery->cost,
                                    'type'        => $orderDelivery->type,
                                    'tariff-id'   => $orderDelivery->tariff_id,
                                    'disabled'    => $disabledEdit
                                ]
                            ) ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>