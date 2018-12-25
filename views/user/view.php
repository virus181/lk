<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $shops app\models\Shop[] */

$this->title = Yii::t('app', 'Update User');
?>
<div class="user-view">
    <title><?= Html::encode($this->title) ?></title>
    <h1><?= Html::encode(Yii::t('app', 'User: {name}', ['name' => $model->fio])) ?></h1>
    <div class="user-form">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group field-shop-name required">
                    <label class="control-label" for="shop-name"><?=Yii::t('app', 'Fio');?></label>
                    <p><strong><?=$model->fio;?></strong></p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group field-shop-name required">
                    <label class="control-label" for="shop-name"><?=Yii::t('app', 'Email');?></label>
                    <p><strong><?=$model->email;?></strong></p>
                </div>
            </div>
        </div>
        <?php if ($model->access_token): ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <span class="label_apikey num"><?= Yii::t('app', 'Your access_token:') ?></span><span class="apikey num"><?= $model->access_token ?></span><i class="fa fa-refresh apikey-refresh"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-group text-center mt15 mb0">
            <?= Html::a('<i class="fa fa-check"></i> ' . Yii::t('app', 'Edit'), Url::to(['update', 'id' => $model->id]), ['class' => 'btn btn-warning']) ?>
        </div>
    </div>

    <?php
    $accessTokenresetUrl = Url::to(['user/reset-access-token', 'id' => $model->id]);
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
</div>
