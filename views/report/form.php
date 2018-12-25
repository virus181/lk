<?php
use app\widgets\Html;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var $order \app\models\search\OrderSearch
 * @var $shops \app\models\Shop[]
 * @var $carriers \app\models\Delivery[]
 * @var $deliveryMethods array
 */

$this->title = Yii::t('app', 'Generate report');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => Url::to(['report/index'])];;
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(); ?>
<?php $form = ActiveForm::begin(); ?>
<div class="page-top order hidden-print" style="position: relative;">
    <?= Html::submitButton('<i class="fa fa-check"></i> ' . Yii::t('app', 'Export'), [
        'class' => 'btn btn-warning'
    ]) ?>
</div>
<div class="report-form">
    <div class="row">
        <div class="col-xs-12">
            <div class="row block">
                <div class="row">
                    <div class="col-xs-12">
                            <div class="col-xs-6">
                                <h2><?= $this->title;?></h2>
                            </div>
                            <?php if(isset($isAllowProduct) && $isAllowProduct):?>
                            <div class="col-xs-6 switch">
                                <?=
                                $form->field($order, 'isWithProducts')->widget(SwitchInput::classname(), [
                                    'inlineLabel' => false,
                                    'containerOptions' => [
                                        'class' => 'switch-input'
                                    ],
                                    'pluginOptions' => [
                                        'size' => 'mini',
                                        'onText' => 'Вкл',
                                        'offText' => 'Выкл'
                                    ],
                                ])->label(false);
                                ?>
                                <label>Выгрузить с продуктами</label>
                            </div>
                            <?php endif;?>
                    </div>
                </div>

                <div class="col-xs-6">
                    <?=
                    $form->field($order, 'shop_id')->widget(Select2::className(), [
                        'data' => $shops,
                        'options' => [
                            'multiple' => true
                        ],
                        'showToggleAll' => false,
                        'pluginOptions' => [
                            'closeOnSelect' => false,
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($order, 'date_created')->widget(DateRangePicker::classname(), [
                        'convertFormat' => false,
                        'useWithAddon' => false,
                        'options' => ['class' => 'form-control', 'id' => 'order-create-date'],
                        'pluginOptions' => [
                            'locale' => [
                                'format' => 'DD.MM.YYYY',
                                'separator' => ' - ',
                            ]
                        ]
                    ]) ?>
                </div>
                <div class="col-xs-6" style="position: relative;">
                    <?=
                    $form->field($order, 'carrier_key')->widget(Select2::className(), [
                        'data' => $carriers,
                        'options' => [
                            'multiple' => true
                        ],
                        'showToggleAll' => false,
                        'pluginOptions' => [
                            'closeOnSelect' => false,
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-xs-6" style="position: relative;">
                    <?=
                    $form->field($order, 'type')->widget(Select2::className(), [
                        'data' => $deliveryMethods,
                        'options' => [
                            'multiple' => true
                        ],
                        'showToggleAll' => false,
                        'pluginOptions' => [
                            'closeOnSelect' => false,
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>

