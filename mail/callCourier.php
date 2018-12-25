<?php

/* @var $this yii\web\View */
/* @var $order \app\models\Order */

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
    <p>
        В магазине "<?php echo $order->shop->name;?>",
        создан новый заказ №<a href="<?php echo \yii\helpers\Url::to(['order/view', 'id' => $order->id], true);?>"><?php echo $order->id;?></a>
    </p>
    <div class="block">
        <table style="vertical-align: top">
            <tr>
                <td><span>Номер заказа:</span><br /> <strong><?php echo $order->shop_order_number;?></strong></td>
                <td><span>Клиент (ФИО):</span><br /> <?php echo $order->fio;?></td>
            </tr>
            <tr>
                <td><span>Email:</span><br /> <strong><?php echo $order->email;?></strong></td>
                <td><span>Телефон:</span><br /> <?php echo $order->phone;?></td>
            </tr>
            <tr>
                <td colspan="2"><span>Магазин:</span><br /> <?php echo $order->shop->name;?></td>
            </tr>
        </table>
    </div>

    <?php if($order->products):?>
    <div class="block">
        <p>Товары:</p>
        <table>
            <tr>
                <td colspan="6">
                    <span>Склад:</span><br />
                    <?php echo ($order->warehouse && $order->warehouse->address) ? $order->warehouse->address->full_address : '---';?>
                </td>
            </tr>
            <?php foreach($order->products as $product):?>
            <tr>
                <td><span>Артикул:</span><br /> <?php echo $product->barcode;?></td>
                <td><span>Название:</span><br /> <?php echo $product->name;?></td>
                <td><span>Цена:</span><br /> <?php echo Yii::$app->formatter->asCurrency((float) $product->price, 'RUB');?></td>
                <td><span>Оценочная стоимость:</span><br /> <?php echo Yii::$app->formatter->asCurrency($product->accessed_price, 'RUB');?></td>
                <td><span>Вес:</span><br /> <?php echo $product->weight / 1000;?> кг.</td>
                <td><span>Кол-во:</span><br /> <?php echo $product->quantity;?></td>
            </tr>
            <?php endforeach;?>
        </table>
    </div>
    <?php endif;?>

    <?php if($order->delivery):?>
    <div class="block">
        <p>Доставка:</p>
        <table>
            <tr>
                <td colspan="3"><span>Адрес:</span><br /> <?php echo $order->address->full_address;?></td>
            </tr>
            <tr>
                <td></td>
                <td>Отгрузка</td>
                <td>Доставка</td>
                <td>Стоимость</td>
            </tr>
            <tr>
                <td rowspan="2"><span>Служба доставки:</span><br /> <?php echo $order->delivery->getDeliveryName();?></td>
                <td><span>Дата отгрузки:</span><br /> <?php echo date('d.m.Y', $order->delivery->pickup_date);?></td>
                <td><span>Расчетная дата доставки:</span><br /> <?php echo $order->delivery->delivery_date ? date('d.m.Y', $order->delivery->delivery_date) : '---';?></td>
                <td><span>Стоимость доставки:</span><br /> <?php echo Yii::$app->formatter->asCurrency($order->delivery->original_cost, 'RUB');?></td>
            </tr>
            <tr>
                <td><span>Способ отгрузки:</span><br /> Забор курьером</td>
                <td><span>Способ доставки:</span><br /> <?php echo $order->delivery->getDeliveryTypeName();?></td>
                <td></td>
            </tr>
        </table>
    </div>
    <?php endif;?>
    <div class="block">
        <p>Оплата:</p>
        <table>
            <tr><td><span>Способ оплаты:</span><br /> <?php echo $order->getPaymentMethod();?></td></tr>
            <tr><td><span>Стоимость товаров:</span><br /> <?php echo Yii::$app->formatter->asCurrency($order->getProductCodCost(), 'RUB');?></td></tr>
            <tr><td><span>Оценочная стоимость:</span><br /> <?php echo Yii::$app->formatter->asCurrency($order->getAssessed_cost(), 'RUB');?></td></tr>
            <?php if($order->delivery): ?>
            <tr><td><span>Стоимость доставки покупателя:</span><br /> <?php echo Yii::$app->formatter->asCurrency($order->delivery->cost, 'RUB');?></td></tr>
            <?php endif;?>
            <tr><td><span>Наложенный платеж:</span><br /> <?php echo Yii::$app->formatter->asCurrency($order->getCodCost(), 'RUB');?></td></tr>
        </table>
    </div>
</div>