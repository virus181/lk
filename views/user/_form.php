<?php

use app\models\User;
use app\widgets\ActiveForm;
use app\widgets\Alert;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $shops app\models\Shop[] */
/* @var $form yii\widgets\ActiveForm */
/* @var $available array */
/* @var $manager \app\models\Manager */

?>
<?= Alert::widget() ?>
<?php $form = ActiveForm::begin(); ?>
<div class="page-top order hidden-print" style="position: relative;">
    <?= Html::submitButton('<i class="fa fa-check"></i> ' . Yii::t('app', 'Save'), ['class' => 'btn btn-warning btn-sm']) ?>
    <div class="workflow">
        <?php if ($user->access_token): ?>
            <div class="form-group access-token-form-group">
                <span class="label_apikey num"><?= Yii::t('app', 'Your access_token:') ?></span>
                <span class="apikey num"><?= $user->access_token ?></span>
                &nbsp;<i class="fa fa-refresh apikey-refresh"></i>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="user-form">
    <div class="col-sm-12">
    <div class="row block">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-6">
                    <h2><?= ($user->fio) ? Html::encode($user->fio) : $this->title ?></h2>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($user, 'fio')->textInput() ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($user, 'email')->textInput() ?>
                </div>
                <?php if ($user->scenario !== User::SCENARIO_SELF_UPDATE) : ?>
                    <div class="col-sm-6">
                        <div class="select_wrapper_sm with_label relative">
                            <?= $form->field($user, 'role')->dropDownList($user->getAllowedRoles()) ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="select_wrapper_sm with_label relative">
                            <?= $form->field($user, 'status')->dropDownList($user->getStatuses()) ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <?=
                        $form->field($user, 'shopIds')->widget(Select2::className(), [
                            'data'          => $shops,
                            'options'       => ['multiple' => true],
                            'showToggleAll' => false,
                            'pluginOptions' => [
                                'closeOnSelect' => false,
                            ],
                        ]);
                        ?>
                    </div>
                <?php endif; ?>
                <div class="col-sm-6">
                    <div class="checkbox-wrapper">
                        <?= $form->field($user, 'notify')->checkbox() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($available['canChangeOperatorInfo']): ?>
        <div class="row block">
            <div class="col-xs-12">
                <h2>Настройки оператора</h2>
            </div>
            <div class="col-xs-4">
                <?= $form->field($user, 'internal_number')->textInput() ?>
            </div>
            <div class="col-xs-4">
                <div class="select_wrapper_sm with_label relative">
                    <?= $form->field($user, 'group_id')->dropDownList($manager->getGroupIds()) ?>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="select_wrapper_sm with_label relative">
                    <?= $form->field($user, 'location')->dropDownList($manager->getManagerLocations()) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php
$accessTokenresetUrl = Url::to(['user/reset-access-token', 'id' => $user->id]);
$this->registerJs(<<<JS
        refresh = '.apikey-refresh';
        $(refresh).on('click', function() {
            var conf = confirm('Смена ключа потребует изменений в коде вашей интеграции! Вы уверены что хотите изменить ключ интеграции?'),
                spin = $(this);
            if (conf) {
                spin.addClass('fa-spin');
                $.ajax({
                    url: '$accessTokenresetUrl',
                    method: 'GET',
                    success: function(access_token) {
                        spin.removeClass('fa-spin');
                        $('.apikey').text(access_token);
                    },
                    error: function(data) {
                        spin.removeClass('fa-spin');
                        alert(data.responseText);
                    }
                })
            }
        });
        $('span.apikey').on('click', function() {
            var r = document.createRange();
            r.selectNode(this);
            document.getSelection().addRange(r);
        });
JS
);
?>
