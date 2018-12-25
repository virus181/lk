<?php

use app\assets\ShopAsset;
use app\delivery\DeliveryHelper;
use app\models\Shop;
use app\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

ShopAsset::register($this);
\yii\widgets\MaskedInputAsset::register($this);

/* @var $isActive boolean */
/* @var $this yii\web\View */
/* @var $shop app\models\Shop */
/* @var $form yii\widgets\ActiveForm */
/* @var $warehouses app\models\Warehouse[] */
/* @var $roundingItems array */
/* @var $rights array */

?>
<?php $form = ActiveForm::begin(); ?>
<?php Yii::$app->domParams->getContextValues($queryParams); ?>
<div class="page-top order hidden-print" style="position: relative;">
    <?= Html::submitButton('<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'), [
        'class'    => 'btn btn-warning btn-sm',
        'disabled' => !$isActive
    ]) ?>
    <div class="workflow">
        <div class="btn-group">
            <?php if (isset($rights['canViewShopUser']) && $rights['canViewShopUser']): ?>
                <?=
                Html::button(Yii::t('shop', 'Get user list'), [
                    'class'       => 'btn btn-default btn-sm',
                    'data-href'   => Url::to(['shop/user-list', 'id' => $shop->id]),
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                ]);
                ?>
            <?php endif; ?>
            <?php if (isset($rights['canBlockShop']) && $rights['canBlockShop'] && $shop->id && $shop->status == Shop::STATUS_ACTIVE): ?>
                <?= Html::a(
                    'Заблокировать',
                    [
                        'shop/block',
                        'id' => $shop->id
                    ],
                    [
                        'class'       => 'btn btn-sm btn-danger',
                        'title'       => 'Деактивирует магазин. В отключенном магазине нельзя создать заказ, так же он не выводится в списках.',
                        'data-toggle' => 'tooltip-status'
                    ]
                ); ?>
            <?php endif; ?>
        </div>

    </div>
</div>
<div class="account-form">
    <div class="col-sm-8">
        <div class="row">
            <div class="col-sm-12">
                <div class="row block">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <h2><?= ($shop->name) ? Html::encode($shop->name) : $this->title ?></h2>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <?= $form->field($shop, 'name')->textInput(['disabled' => !$isActive]) ?>
                                <div class="select_wrapper_sm with_label relative">
                                    <?= $form->field($shop, 'default_warehouse_id')->dropDownList($warehouses, ['disabled' => !$isActive]) ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <?= $form->field($shop, 'url')->textInput(['disabled' => !$isActive]) ?>
                                <?= $form->field($shop, 'phone', [
                                    'template' => "{label}\n<div class=\"input-group\">{input}\n<span class=\"input-group-btn\"><button class=\"btn btn-primary\" type=\"button\"><i class='glyphicon glyphicon-earphone'></i></button></span></div>",
                                ])->widget(
                                    MaskedInput::className(),
                                    [
                                        'mask'    => '+7 (999) 999-99-99', 'clientOptions' => ['onincomplete' => 'function(){$("#shop-phone").removeAttr("value").attr("value","");}'],
                                        'options' => [
                                            'class'       => 'form-control',
                                            'placeholder' => '+7 (___) ___-__-__',
                                            'disabled'    => !$isActive
                                        ]
                                    ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <?=
                                $form->field($shop, 'warehouseIds')->widget(Select2::className(), [
                                    'data'          => $warehouses,
                                    'options'       => [
                                        'multiple' => true,
                                        'disabled' => !$isActive
                                    ],
                                    'showToggleAll' => false,
                                    'pluginOptions' => [
                                        'closeOnSelect' => false,
                                    ],
                                ]);
                                ?>
                            </div>
                            <div class="col-sm-6">
                                <?= $form->field($shop, 'process_day')
                                    ->textInput([
                                        'type'           => 'number',
                                        'min'            => 0,
                                        'title'          => 'Указание количества дней подготовки отправления добавляется к срокам доставки для более корректного отображения предполагаемой даты доставки.
',
                                        'data-toggle'    => 'tooltip',
                                        'data-trigger'   => 'focus',
                                        'data-placement' => 'bottom'
                                    ]); ?>
                            </div>
                        </div>
                        <?php if (isset($rights['canUpdateSkladId']) && $rights['canUpdateSkladId']): ?>
                            <div class="row">
                                <div class="col-sm-6">
                                    <?= $form->field($shop, 'additional_id')
                                        ->textInput([
                                            'type' => 'number',
                                            'min'  => 0
                                        ]); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="row block">
                            <div class="col-sm-12">
                                <h2><?php echo Yii::t('shop', 'Legal information');?></h2>
                                <?= $form->field($shop, 'legal_entity')->textInput(['disabled' => !$isActive]) ?>
                                <?= $form->field($shop, 'inn')->textInput(['disabled' => !$isActive]) ?>
                                <?= $form->field($shop, 'kpp')->textInput(['disabled' => !$isActive]) ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($shop->id): ?>
                        <div class="col-sm-12">
                            <div class="row block">
                                <div class="col-xs-6">
                                    <h2><?php echo Yii::t('shop', 'Tariffication');?></h2>
                                </div>
                                <div class="col-xs-6">
                                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('shop', 'Edit rates'), ['shop/block', 'id' => $shop->id], [
                                        'class'       => 'btn btn-sm btn-primary pull-right',
                                        'data-href'   => Url::to(['tariff/add', 'shopId' => $shop->id]),
                                        'data-toggle' => 'modal',
                                        'data-target' => '#modal',
                                    ]); ?>
                                </div>
                                <div class="col-sm-12 delivery-service">
                                    <?php foreach ($tariffs as $key => $tariff): ?>
                                        <div class="row tariff-table">
                                            <div class="col-xs-9">
                                                <p class="tariff-name"><?= \app\models\Tariff::getTariffName($tariff); ?></p>
                                            </div>
                                            <div class="col-xs-3">
                                                <?= Html::a('<i class="fa fa-trash"></i>', ['#'], [
                                                    'data-href'   => Url::to(['tariff/remove', 'shopId' => $shop->id, 'tariffId' => $tariff->id]),
                                                    'data-toggle' => 'modal',
                                                    'data-target' => '#modal',
                                                    'class'       => 'btn btn-sm btn-default pull-right'
                                                ]);
                                                ?>
                                                <?= Html::a('<i class="fa fa-pencil"></i>', ['#'], [
                                                    'data-href'   => Url::to(['tariff/add', 'shopId' => $shop->id, 'tariffId' => $tariff->id]),
                                                    'data-toggle' => 'modal',
                                                    'data-target' => '#modal',
                                                    'class'       => 'btn btn-sm btn-default pull-right'
                                                ]);
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="row block">
                                <div class="col-xs-6">
                                    <h2><?php echo Yii::t('shop', 'Own delivery');?></h2>
                                </div>
                                <div class="col-xs-6">
                                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('shop', 'Edit rates'), ['rate/add', 'id' => $shop->id], [
                                        'class'       => 'btn btn-sm btn-primary pull-right',
                                        'data-href'   => Url::to(['rate/add', 'shopId' => $shop->id]),
                                        'data-toggle' => 'modal',
                                        'data-target' => '#modal',
                                    ]); ?>
                                </div>
                                <div class="col-sm-12 courier-service">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($rights['canUpdateTariffs']): ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row block">
                                <div class="col-sm-6">
                                    <h2><?= Yii::t('shop', 'Phone and fulfillment services'); ?></h2>
                                </div>
                                <div class="col-sm-6 text-right">
                                    <div class="swtich">
                                        <?= $form
                                            ->field($shop, 'fulfillment')
                                            ->widget(SwitchInput::classname(), [
                                                'pluginOptions' => [
                                                    'size'    => 'mini',
                                                    'onText'  => 'Вкл',
                                                    'offText' => 'Выкл',
                                                ],
                                                'inlineLabel'   => true,
                                                'labelOptions'  => ['style' => 'font-size: 12px'],
                                            ])->label(false); ?>
                                    </div>
                                </div>

                                <div class="col-sm-12 text-right">
                                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('shop', 'Edit tariff'), ['shop/tariff-update', 'id' => $shop->id], [
                                        'class'       => 'btn btn-sm btn-primary pull-right',
                                        'data-href'   => Url::to(['shop/tariff-update', 'id' => $shop->id]),
                                        'data-toggle' => 'modal',
                                        'data-target' => '#modal',
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($rights['canUpdatePhones']): ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row block">
                                <div class="col-sm-6">
                                    <h2><?= Yii::t('shop', 'Phones'); ?></h2>
                                </div>
                                <div class="col-sm-6 text-right">
                                    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('shop', 'Edit phone'), ['shop/phone-update', 'id' => $shop->id], [
                                        'class'       => 'btn btn-sm btn-primary pull-right',
                                        'data-href'   => Url::to(['shop/phone-update', 'id' => $shop->id]),
                                        'data-toggle' => 'modal',
                                        'data-target' => '#modal',
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="row block">
            <div class="col-sm-12 delivery-service">
                <h2><?php echo Yii::t('shop', 'Allowed delivery types');?></h2>
                <div class=" switch-checkout-group">
                    <?php
                    foreach ($deliveryTypes as $key => $type) {
                        echo $form->field($shop, 'types[' . $type['value'] . ']')->widget(SwitchInput::classname(), [
                            'value'         => 1,
                            'type'          => SwitchInput::CHECKBOX,
                            'pluginOptions' => [
                                'size'    => 'mini',
                                'onText'  => 'Вкл',
                                'offText' => 'Выкл',
                            ]
                        ])->label(false);
                        ?>
                        <label><?= $type['label']; ?></label>
                        <div class="clear"></div>
                        <?php
                    }
                    ?>
                </div>
                <hr/>
                <h2><?php echo Yii::t('shop', 'Deliveries');?></h2>
                <?= $form->field($shop, 'deliveries')->checkboxList(ArrayHelper::map($deliveries, 'id', 'carrier_key'),
                    [
                        'item' => function ($index, $label, $name, $checked, $value) use ($deliveries, $shop, $isActive, $rights) {

                            $isEnabled = function ($carrierKey) use ($deliveries) {
                                foreach ($deliveries as $delivery) {
                                    if ($delivery['carrier_key'] == $carrierKey) {
                                        return (boolean)$delivery['status'];
                                    }
                                }
                                return false;
                            };

                            $deliveryDescription = function ($carrierKey) use ($deliveries) {
                                foreach ($deliveries as $delivery) {
                                    if ($delivery['carrier_key'] == $carrierKey) {
                                        return $delivery['description'];
                                    }
                                }
                                return '';
                            };

                            if (!$shop->id && $isEnabled($label)) {
                                $checked = true;
                            }

                            $inputId = str_replace(['[', ']'], ['', ''], $name) . '_' . $index;

                            $disabled = (isset($rights['canUpdateDelivery']) && $rights['canUpdateDelivery'] && $isActive) ? false : true;

                            $template = '<div class="quote row ' . ((!$checked) ? 'unchecked-row' : '') . '" >';
                            $template .= '<div class="col-sm-1 checkbox">';
                            if (!$shop->id) {
                                $template .= Html::checkbox($name, $checked, [
                                    'value' => $value,
                                    'id'    => $inputId,
                                    'class' => 'hidden'
                                ]);
                            }
                            $template .= Html::checkbox($name, $checked, [
                                'value'    => $value,
                                'id'       => $inputId,
                                'disabled' => $disabled
                            ]);

                            $template .= '</div>';
                            $template .= '<div class="img col-sm-3"><img src="' . DeliveryHelper::getIconPath($label) . '"/> </div>';
                            $template .= '<div class="desc col-sm-offset-1 col-sm-6">';
                            $template .= '<p class="name num">' . DeliveryHelper::getName($label) . '</p>';
                            $template .= '<span class="subname num">' . $deliveryDescription($label) . '</span>';
                            $template .= '</div>';
                            $template .= '</div>';
                            return $template;
                        }
                    ]
                )->label(false) ?>
                <hr>
                <h2><?php echo Yii::t('shop', 'Delivery cost rounding');?></h2>
                <div class="switch-radio">
                    <?=
                    $form->field($shop, 'rounding_off_prefix')->widget(SwitchInput::classname(), [
                        'type'          => SwitchInput::RADIO,
                        'inlineLabel'   => false,
                        'items'         => $roundingItems,
                        'labelOptions'  => ['style' => 'font-size: 12px'],
                        'separator'     => '<div class="separator"></div>',
                        'pluginOptions' => [
                            'size'    => 'mini',
                            'onText'  => 'Вкл',
                            'offText' => 'Выкл',
                        ],
                    ])->label(false);
                    ?>
                </div>
                <hr/>
                <div class="switch-radio">
                    <?=
                    $form->field($shop, 'rounding_off')->widget(SwitchInput::classname(), [
                        'type'          => SwitchInput::RADIO,
                        'inlineLabel'   => false,
                        'items'         => $roundingItemValues,
                        'labelOptions'  => ['style' => 'font-size: 12px'],
                        'separator'     => '<div class="separator"></div>',
                        'pluginOptions' => [
                            'size'    => 'mini',
                            'onText'  => 'Вкл',
                            'offText' => 'Выкл',
                        ],
                    ])->label(false);
                    ?>
                </div>
                <hr>
                <h2><?php echo Yii::t('shop', 'Delivery cost rounding');?></h2>
                <div class="swtich">
                    <?= $form
                        ->field($shop, 'parse_address')
                        ->widget(SwitchInput::classname(), [
                            'pluginOptions' => [
                                'size'    => 'mini',
                                'onText'  => 'Вкл',
                                'offText' => 'Выкл',
                            ],
                            'inlineLabel'   => true,
                            'labelOptions'  => ['style' => 'font-size: 12px'],
                        ])->label(false); ?>
                    <label>Автоматический разбор адреса</label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
