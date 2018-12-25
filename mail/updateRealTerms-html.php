<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $order \app\models\Order */
/* @var $courier \app\models\Courier */
?>
<style type="text/css">
    .html-wrapper {
        height: 100%;
        padding: 20px;
        background-color: #f6fafb;
        font-size: 14px;
        color: #333;
    }

    .html-wrapper span {
        font-size: 12px;
        color: #8a8a8a;
        font-weight: 300;
    }

    .html-wrapper .block {
        background-color: #fff;
        box-shadow: 0 0 5px #CCC !important;
        -moz-box-shadow: 0 0 5px #CCC !important;
        -webkit-box-shadow: 0 0 5px #CCC !important;
        padding: 20px 5px 20px 5px;
        margin-bottom: 20px;
    }

    .html-wrapper table {
        border-collapse: collapse;
        width: 100%;
    }

    .html-wrapper p {
        padding: 0 15px;
    }

    .html-wrapper td {
        padding: 10px 15px;
    }
</style>
<div class="html-wrapper">
    <p style="text-align: center"><img src="http://lk.fastery.ru/logo.png" height="90px;" /></p>
    <p><?=$text;?></p>
</div>