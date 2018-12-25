<?php
use app\assets\AppAsset;
use \app\delivery\DeliveryHelper;
use \app\models\Helper;
use yii\helpers\Url;
/* @var \app\models\Courier $courier; */
?>
<table id="registry-list">
    <tr>
        <td><h2>Реестр <?=DeliveryHelper::getName($courier->carrier_key);?> №<?=$courier->id;?></h2></td>
    </tr>
    <tr>
        <td><h3>для передачи отправок от Отправителя представителю <?=DeliveryHelper::getName($courier->carrier_key);?></h3></td>
    </tr>
    <tr><td><br /></td></tr>
    <tr>
        <td class="inner-cell">
            <table>
                <tr>
                    <td width="50%">
                        <table class="col-xs-12 table-bordered">
                            <tr>
                                <td>Название отправителя: <?=$courier->orders[0]->shop->legal_entity ? $courier->orders[0]->shop->legal_entity : $courier->warehouse->name; ?></td>
                            </tr>
                            <tr>
                                <td>Адрес отправителя: <?=$courier->warehouse->address->full_address;?></td>
                            </tr>
                            <tr>
                                <td>Контактное лицо: <?=$courier->warehouse->contact_fio;?></td>
                            </tr>
                            <tr>
                                <td>Телефон: <?=$courier->warehouse->contact_phone;?></td>
                            </tr>
                            <tr>
                                <td>Дата приема груза: <?=date('d.m.Y', $courier->pickup_date);?></td>
                            </tr>
                        </table>
                    </td>
                    <td width="50%">
                        <img class="img-responsive" src="<?= DeliveryHelper::getIconThumbPath($courier->carrier_key);?>"/>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="100%" class="table-bordered order-list text-center">
                <thead>
                <tr>
                    <th width="9%">№ ИМ</th>
                    <th width="9%">№ Fastery</th>
                    <th width="9%">№ отправки</th>
                    <th>Кол-во мест</th>
                    <th>Объявленная ценность</th>
                    <th>Грузополучатель</th>
                    <th>Город доставки</th>
                    <th>Адрес получателя</th>
                    <th>Сумма к оплате получателем</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($courier->orders as $order):?>
                    <tr>
                        <td><?=$order->shop_order_number;?></td>
                        <td><?=$order->id;?></td>
                        <td><?=($order->dispatch_number) ? $order->dispatch_number : '---';?></td>
                        <td>1</td>
                        <td><?=$order->getAssessed_cost();?></td>
                        <td><?=$order->fio;?></td>
                        <td><?=$order->address->city;?></td>
                        <td><?=$order->address->full_address;?></td>
                        <td><?=$order->getCodCost();?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="80%">
                <tr>
                    <td width="50%">
                        <table width="100%">
                            <tr>
                                <td width="40%"><b style="white-space: nowrap;">Общее кол-во мест</b></td>
                                <td class="cell-underlined"><?=count($courier->orders);?></td>
                            </tr>
                            <tr>
                                <td width="40%"></td>
                                <td>(числом)</td>
                            </tr>
                            <tr>
                                <td><b>Дата</b></td>
                                <td class="cell-underlined"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="sub-title">Отправитель:</td>
                            </tr>
                            <tr>
                                <td><b>Подпись</b></td>
                                <td class="cell-underlined"></td>
                            </tr>
                            <tr>
                                <td><b>Ф.И.О.</b></td>
                                <td class="cell-underlined"></td>
                            </tr>
                        </table>
                    </td>
                    <td width="50%">
                        <table width="100%">
                            <tr>
                                <td class="cell-underlined" colspan="2"><?=Helper::numberToString(count($courier->orders));?></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>(прописью)</td>
                            </tr>
                            <tr>
                                <td><b>Время</b></td>
                                <td class="cell-underlined"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="sub-title">Представитель <?=DeliveryHelper::getName($courier->carrier_key);?>:</td>
                            </tr>
                            <tr>
                                <td><b>Подпись</b></td>
                                <td class="cell-underlined"></td>
                            </tr>
                            <tr>
                                <td><b>Ф.И.О.</b></td>
                                <td class="cell-underlined"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>