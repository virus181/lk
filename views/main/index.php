<?php

/* @var $this yii\web\View */

use yii\helpers\Url;

$this->title = Yii::t('app', 'Dashboard');
?>
<div class="site-index">
    <div class="row">
        <div class="col-sm-12 head-dash">
            <h1 class="lead">Здравствуйте, <?= Yii::$app->user->identity->fio ?>!</h1>
            <span>Приветствуем Вас в системе Fastery <br>Вам доступны следующие функции:</span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2">
            <a href="<?= Url::to(['/order/index']) ?>">
                <div class="block">
                    <img src="/img/dash/1.png">
                    <div class="text">
                        <p class="name">Заказы</p>
                        <p class="desc">Следите за состоянием всех заказов</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a href="<?= Url::to(['/label/index']) ?>">
                <div class="block">
                    <img src="/img/dash/2.png">
                    <div class="text">
                        <p class="name">Этикетки</p>
                        <p class="desc">Печать этикеток на отправления</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a href="<?= Url::to(['/courier/index']) ?>">
                <div class="block">
                    <img src="/img/dash/4.png">
                    <div class="text">
                        <p class="name">Реестры</p>
                        <p class="desc">Управляйте Вашими отгрузками</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a href="<?= Url::to(['/shop/index']) ?>">
                <div class="block">
                    <img src="/img/dash/6.png">
                    <div class="text">
                        <p class="name">Магазины</p>
                        <p class="desc">Добавляйте и настраивайте параметры магазинов</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a href="<?= Url::to(['warehouse/index']) ?>">
                <div class="block">
                    <img src="/img/dash/3.png">
                    <div class="text">
                        <p class="name">Склады</p>
                        <p class="desc">Добавляйте и изменяйте адреса отгрузок</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a href="<?= Url::to(['/user/index']) ?>">
                <div class="block">
                    <img src="/img/dash/7.png">
                    <div class="text">
                        <p class="name">Пользователи</p>
                        <p class="desc">Контактные данные и настройка профиля</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a>
                <div class="block disabled">
                    <img src="/img/dash/8.png">
                    <div class="text">
                        <p class="name">Бухгалтерия</p>
                        <p class="desc">Отчетность и бухгалтерские документы</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a href="<?= Url::to(['/product/index']) ?>">
                <div class="block">
                    <img src="/img/dash/11.png">
                    <div class="text">
                        <p class="name">Товары</p>
                        <p class="desc">Добавляйте товары и следите за остатками</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a>
                <div class="block disabled">
                    <img src="/img/dash/12.png">
                    <div class="text">
                        <p class="name">Телефония</p>
                        <p class="desc">Прослушивайте записи разговоров операторов</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-2">
            <a>
                <div class="block disabled">
                    <img src="/img/dash/10.png">
                    <div class="text">
                        <p class="name">Аналитика</p>
                        <p class="desc">Отчеты по вашим заказам в системе</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
