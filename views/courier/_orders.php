<?php

use app\delivery\DeliveryHelper;
use app\models\Order;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\ModalGridView;
use app\workflow\WorkflowHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var \app\models\Order $order */

$this->title = Yii::t('app', 'The Courier Orders');
$columns = [
    [
        'class' => CheckboxColumn::className(),
        'headerOptions' => [
            'width' => '30px',
        ],
    ],
    [
        'attribute' => 'created_at',
        'content' => function (Order $order) {
            return Html::a(date('d.m.Y, H:i', $order->created_at), Url::to(['view', 'id' => $order->id]));
        },
    ],
    [
        'attribute' => 'id',
        'label' => Yii::t('app', 'Fastery number'),
        'content' => function (Order $order) {
            return Html::a($order->id, Url::to(['view', 'id' => $order->id]));
        },
    ],
    [
        'attribute' => 'shop_order_number',
        'label' => Yii::t('app', 'Shop number'),
        'content' => function (Order $order) {
            return Html::a($order->shop_order_number, Url::to(['view', 'id' => $order->id]));
        },
    ],
    [
        'attribute' => 'cost',
        'label' => Yii::t('app', 'Order Amount'),
        'content' => function (Order $model) {
            return Yii::$app->formatter->asCurrency($model->cost, 'RUB');
        },
    ],
    [
        'attribute' => 'fio',
        'label' => Yii::t('app', 'fio'),
        'contentOptions' => [
            'class' => 'frr',
            'style' => 'overflow: hidden',
        ],
    ],
    [
        'attribute' => 'phone',
        'label' => Yii::t('app', 'Phone'),
        'content' => function (Order $model) {
            return '<span style="white-space: nowrap;">' . $model->getNormalizePhone() . '</span>';
        }
    ],
    [
        'attribute' => 'codCost',
        'label' => 'Сумма',
        'content' => function (Order $model) {
            return Yii::$app->formatter->asCurrency($model->getCodCost(), 'RUB');
        }
    ],
    [
        'attribute' => 'shop_id',
        'label' => Yii::t('app', 'Shop'),
        'content' => function (Order $model) {
            return $model->shop->name;
        },
        'filterOptions' => [
            'style' => 'position: relative;',
        ],
        'filter' => Html::tag('div', Html::dropDownList(
            'shop_id',
            $searchModel->shop_id,
            array_replace([null => ''], Yii::$app->user->identity->getAllowedShops()),
            [
                'class' => 'form-control input-xs' . (($searchModel->shop_id === null || $searchModel->shop_id === '') ? '' : ' selected'),
            ]
        ), ['class' => 'select_wrapper'])
    ]
];
?>

<div class="courier-call">
    <?= Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Add orders to Registry'), '#', [
        'class' => 'btn btn-warning btn-sm pull-right add-orders',
        'data-courier-id' => $courierId
    ]) ?>
    <title><?=Yii::t('app', 'Select orders for add to registry file');?></title>
    <h1><?=Yii::t('app', 'Select orders for add to registry file');?></h1>
    <div class="row">
        <?php Pjax::begin([
            'linkSelector' => false,
        ]); ?>
        <?=
        ModalGridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => $columns,
        ]);
        ?>
        <?php Pjax::end(); ?>
    </div>

</div>
