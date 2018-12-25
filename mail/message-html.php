<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $message \app\models\Message */
?>
<div class="password-reset">
    <p>Новое сообщение от <?=$message->fio;?></p>
    <hr />
    <p><?=$message->title;?></p>
    <p><?=$message->text;?></p>
    <hr />
    <p>Тип сообщения: <?= $message::getMessageTypes()[$message->type] ?></p>
    <p>Id пользователя: <?= $message->user_id ?></p>
    <p>Дата отправки: <?= date('d.m.Y в H:i', $message->created_at) ?></p>
    <p>Телефон: <?= $message->phone ?></p>
</div>