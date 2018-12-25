<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $invoice app\models\Repository\Invoice */

$this->title = Yii::t('registry', 'Invoice {id}', ['id' => $invoice->number]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('registry', 'Registry'), 'url' => Url::to(['account/index'])];
$this->params['breadcrumbs'][] = Yii::t('registry', 'Invoice <span class="num">№ {id}</span>', ['id' => $invoice->number]);

?>
<div class="account-update">
    <title><?= Html::encode($this->title) ?></title>
    <?php $form = ActiveForm::begin(); ?>
    <div class="page-top order hidden-print">
        <?= Html::button('<i class="fa fa-check"></i> ' . Yii::t('app', 'Download'), [
            'class' => 'btn btn-warning btn-sm'
        ]) ?>
    </div>
    <div class="account-form">
        <div class="col-sm-8">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row block">
                        <div class="col-xs-12">
                            <h2><?php echo Yii::t('registry', 'Orders'); ?></h2>
                        </div>
                        <div class="col-xs-12">
                            <div class="orders">
                                <table class="table no-shadow-table">
                                    <thead>
                                    <tr>
                                        <td><?php echo Yii::t('registry', 'Order ID'); ?></td>
                                        <td><?php echo Yii::t('registry', 'Shop order number'); ?></td>
                                        <td><?php echo Yii::t('registry', 'Delivery cost'); ?></td>
                                        <td><?php echo Yii::t('registry', 'Fastery charge'); ?></td>
                                        <td><?php echo Yii::t('registry', 'Product cost'); ?></td>
                                        <td class="text-right"><?php echo Yii::t('registry', 'Total'); ?></td>
                                    </tr>
                                    </thead>
                                    <?php foreach ($invoice->orders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo \yii\helpers\Url::to(['order/view', 'id' => $order->order_id]); ?>">
                                                    <?php echo $order->order_id; ?>
                                                </a></td>
                                            <td><?php echo $order->order->shop_order_number;?></td>
                                            <td><?php echo Yii::$app->formatter->asCurrency($order->delivery_cost, 'RUB'); ?></td>
                                            <td><?php echo Yii::$app->formatter->asCurrency($order->fastery_charge, 'RUB'); ?></td>
                                            <td><?php echo Yii::$app->formatter->asCurrency($order->product_cost, 'RUB'); ?></td>
                                            <td class="text-right"><?php echo Yii::$app->formatter->asCurrency($order->total, 'RUB'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right"></td>
                                        <td class="text-right"><strong><?= $invoice->getDocumentSum(); ?><strong></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="row block">
                <div class="col-sm-12">
                    <h2><?php echo $invoice->getDocumentType(); ?>: <?php echo $invoice->number; ?></h2>

                    <div class="form-group">
                        <label class="control-label"
                               for="order-shop_id"><?php echo Yii::t('registry', 'Invoice type'); ?></label>
                        <p><?= $invoice->getDocumentType(); ?></p>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                               for="order-shop_id"><?php echo Yii::t('registry', 'Invoice number'); ?></label>
                        <p><?= $invoice->number; ?></p>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                               for="order-shop_id"><?php echo Yii::t('registry', 'Registry number'); ?></label>
                        <p><?= $invoice->registry->number; ?></p>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                               for="order-shop_id"><?php echo Yii::t('registry', 'Invoice date'); ?></label>
                        <p><?= $invoice->getDocumentDate(); ?></p>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                               for="order-shop_id"><?php echo Yii::t('registry', 'Summ'); ?></label>
                        <p><?= $invoice->getDocumentSum(); ?></p>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                               for="order-shop_id"><?php echo Yii::t('registry', 'Shop name'); ?></label>
                        <p><?= $invoice->getDocumentShopName(); ?></p>
                    </div>

                    <div class="form-group">
                        <label class="control-label"
                               for="order-shop_id"><?php echo Yii::t('registry', 'Status'); ?></label>
                        <p><?= $invoice->getDocumentStatus(); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
