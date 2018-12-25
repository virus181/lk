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
    <p>Для заказа #<?php echo $order->id;?> был вызван курьер, реестр #<?php echo $courier->id;?></p>
    <div class="block">
        <table>
            <tr>
                <td><span>Реестр:</span><br /> #<?php echo $courier->id;?></td>
                <td><span>Дата забора:</span><br /> <?php echo date("d.m.Y", $courier->pickup_date);?></td>
            </tr>
            <tr>
                <td><span>Номер заказа:</span><br /> #<?php echo $order->id;?></td>
                <td><span>Номер заказа по версии ИМ:</span><br /> <?php echo $order->shop_order_number;?></td>
            </tr>
            <tr>
                <td colspan="2"><span>Клиент (ФИО):</span> <?php echo $order->fio;?></td>
            </tr>
            <tr>
                <td><span>Email:</span><br /> <strong><?php echo $order->email;?></strong></td>
                <td><span>Телефон:</span><br /> <?php echo $order->phone;?></td>
            </tr>
            <tr>
                <td colspan="2"><hr /></td>
            </tr>
            <tr>
                <td colspan="2"><span>Магазин:</span> <?php echo $order->shop->name;?></td>
            </tr>
            <tr>
                <td colspan="2"><span>Склад:</span> <?php echo $courier->warehouse->address->full_address;?></td>
            </tr>
            <tr>
                <td><span>Контактное лицо склада:</span><br /> <?php echo $courier->warehouse->contact_fio;?></td>
                <td><span>Контактный телефон склада:</span><br /> <?php echo $courier->warehouse->contact_phone;?></td>
            </tr>
        </table>
    </div>

    <?php if($order->products):?>
        <div class="block">
            <p>Товары:</p>
            <table>
                <?php foreach($order->products as $product):?>
                    <tr>
                        <td><span>Артикул:</span><br /> <?php echo $product->barcode;?></td>
                        <td><span>Название:</span><br /> <?php echo $product->name;?></td>
                        <td><span>Цена:</span><br /> <?php echo Yii::$app->formatter->asCurrency($product->price, 'RUB');?></td>
                        <td><span>Оценочная стоимость:</span><br /> <?php echo Yii::$app->formatter->asCurrency($product->accessed_price, 'RUB');?></td>
                        <td><span>Вес:</span><br /> <?php echo $product->weight / 1000;?> кг.</td>
                        <td><span>Кол-во:</span><br /> <?php echo $product->quantity;?></td>
                    </tr>
                <?php endforeach;?>
            </table>
        </div>
    <?php endif;?>
</div>