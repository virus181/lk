<?php
/* @var \app\models\Order[] $orders */

use app\delivery\DeliveryHelper;
?>

<?php foreach ($orders as $order):?>
    <div class="label">
        <div class="barcode">
            <?php
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode(
                    $order->dispatch_number,
                    $generator::TYPE_CODE_128,
                    2,
                    40)) . '">';
            ?><br />
            <span><?=$order->dispatch_number;?></span>
        </div>
        <div>
            <div class="fst-number">
                Fastery №<br /><?=$order->id;?>
            </div>
            <div class="shop-number">
                ИМ №<br /><?=$order->shop_order_number;?>
            </div>
        </div>
        <div>
            <div class="order-details">
                <?=$order->fio;?>, <?=$order->address->full_address;?><br /><?=$order->phone;?>
            </div>
        </div>
        <div>
            <div class="fst-delivery">
                Доставка:<br /> <img style="max-width: 100px; max-height: 30px;" src="<?= DeliveryHelper::getIconThumbPath($order->delivery->carrier_key);?>" />
            </div>
        </div>
        <div>
            <div class="fst-warehouse">
                Склад: <?=$order->warehouse->address->full_address;?>
            </div>
        </div>
    </div>
<?php endforeach; ?>