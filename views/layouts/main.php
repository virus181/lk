<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AppAsset;
use app\models\User;
use app\widgets\Alert;
use app\widgets\Modal;
use app\widgets\Nav;
use kartik\switchinput\SwitchInputAsset;
use yii\bootstrap\NavBar;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);
SwitchInputAsset::register($this);
\yii2mod\alert\AlertAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="theme-color" content="#ffffff">
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="wrap">
    <?php
    $logoutButton = Html::beginForm(['/main/logout'], 'post') . Html::submitButton('<i class="fa fa-sign-out"></i>', ['class' => 'btn btn-link logout']) . Html::endForm();
    ?>
    <div class="mainbody">
        <div class="top-row">
            <div class="logo-block">
                <img src="/logo.svg">
            </div>
            <div class="left-block hidden-print">
                <div class="support">
                    <?=
                    Html::a('<i class="fa fa-question-circle-o"></i> ' . Yii::t('app', Yii::t('app', 'Support')),
                        ['message/create'],
                        [
                            'data-href'   => Url::to(['message/create']),
                            'data-toggle' => 'modal',
                            'data-target' => '#modal',
                        ]
                    );
                    ?>
                </div>
                <div class="offer">
                    <a href="javascript:void(0)" data-target="#modal" data-toggle="modal"
                       data-href="<?= Url::to('/main/offer') ?>"><i
                                class="fa fa-file-text-o"></i> <?= Yii::t('app', 'Offer') ?></a>
                </div>
            </div>
            <div class="logout hidden-print">
                <?= $logoutButton ?>
            </div>
            <div class="right-block hidden-print">
                <div class="user">
                    <?= Html::a(
                        '<i class="fa fa-user-o"></i> ' . Yii::$app->user->identity->fio,
                        'javascript:void(0)',
                        [
                            'data-toggle' => 'modal',
                            'data-target' => '#modal',
                            'data-href'   => Url::to(['/user/view', 'id' => Yii::$app->user->id])
                        ]) ?>
                    <a href="#"></a>
                </div>
            </div>
        </div>
        <div class="breadcrumb hidden-print">
            <ul>

                <?php if (isset($this->params['breadcrumbs']) && is_array($this->params['breadcrumbs'])): ?>
                    <?php
                    /** @var array|string $breadcrumb */
                    foreach ($this->params['breadcrumbs'] as $breadcrumb): ?>
                        <li>
                            <a href="<?= (!is_array($breadcrumb) ? 'javascript:void(0)' : ArrayHelper::getValue($breadcrumb, 'url')) ?>">
                                <?= (!is_array($breadcrumb)) ? $breadcrumb : ArrayHelper::getValue($breadcrumb, 'label') ?>
                                <i class="fa fa-angle-right "></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <?= Alert::widget() ?>
        <div class="container-fluid">
            <?= $content ?>
        </div>
        <?php Modal::begin([
            'id'   => 'modal',
            'size' => 'modal-lg',
        ]) ?>
        <div class="text-center"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>
        <?php Modal::end() ?>
        <div class="footer"></div>
    </div>
    <div class="menu">
        <?php
        NavBar::begin([
            'options'              => [
                'class' => 'navbar navbar-default navbar-fixed-left',
            ],
            'renderInnerContainer' => false,
        ]);
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $items = [
            ['label' => '<i class="ic ic-home"></i>', 'url' => ['/'], 'activeController' => false, 'encode' => false],
            [
                'label'            => '<i class="ic ic-list3"></i>',
                'url'              => ['/order/index'],
                'class'            => 'link-order',
                'activeController' => false,
                'encode'           => false
            ],
            ['label' => '<i class="ic ic-code"></i>', 'url' => ['/label/index'], 'activeController' => true, 'encode' => false],
//            ['label' => '<i class="ic ic-deliver"></i>', 'url' => ['/order/ready-for-delivery'], 'activeController' => false, 'encode' => false],
            ['label' => '<i class="ic ic-deliver"></i>', 'url' => ['/courier/index'], 'activeController' => true, 'encode' => false],
            ['label' => '<i class="ic ic-store"></i>', 'url' => ['/shop/index'], 'activeController' => true, 'encode' => false],
            ['label' => '<i class="ic ic-box"></i>', 'url' => ['/warehouse/index'], 'activeController' => true, 'encode' => false],
            ['label' => '<i class="ic ic-avatar"></i>', 'url' => ['/user/index'], 'activeController' => true, 'encode' => false],
            ['label' => '<i class="ic ic-list"></i>', 'url' => '#', 'activeController' => true, 'encode' => false],
            ['label' => '<i class="ic ic-shopping-bag"></i>', 'url' => ['/product/index'], 'activeController' => true, 'encode' => false],
            ['label' => '<i class="ic ic-phone"></i>', 'url' => ['/call/index'], 'activeController' => true, 'encode' => false],
            ['label' => '<i class="fa fa-line-chart"></i>', 'url' => '#', 'activeController' => true, 'encode' => false],
        ];

        $sub = '
                <ul class="sub">
                    <li>Меню</li>
                    <li>' . Html::a('Заказы', Url::to(['/order/index'])) . '</li>
                    <li>' . Html::a('Этикетки', Url::to(['/label/index'])) . '</li>
                    <li>' . Html::a('Реестры', Url::to(['/courier/index'])) . '</li>
                    <li>' . Html::a('Магазины', Url::to(['/shop/index'])) . '</li>
                    <li>' . Html::a('Склады', Url::to(['/warehouse/index'])) . '</li>
                    <li>' . Html::a('Пользователи', Url::to(['/user/index'])) . '</li>
                    <li>' . Html::a('Бухгалтерия (СКОРО)', '#', ['class' => 'no-active']) . '</li>
                    <li>' . Html::a('Товары', Url::to(['/product/index'])) . '</li>
                    <li>' . Html::a('Телефония', Url::to(['/call/index'])) . '</li>
                    <li>' . Html::a('Аналитика (СКОРО)', '#', ['class' => 'no-active']) . '</li>
                </ul>';

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items'   => $items,
        ]);
        NavBar::end();
        echo $sub;
        ?>
    </div>
</div>
<?php $this->endBody() ?>
<script>
    $(function () {
        var config = {attributes: true, childList: true, characterData: true};
        var target = document.querySelector('.modal');
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                $(mutation.target).find('[data-toggle="tooltip"]').tooltip();
            });
        });
        observer.observe(target, config);
    })
</script>
</body>
</html>
<?php $this->endPage() ?>
