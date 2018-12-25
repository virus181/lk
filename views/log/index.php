<?php

use app\models\Log;
use app\models\Shop;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Log */
/* @var $order app\models\Order */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<?php
$columns = [
    [
        'attribute' => 'created_at',
        'label' => Yii::t('app', 'Updated at'),
        'content' => function (Log $searchModel) {
            return date('d.m.Y, H:i', $searchModel->created_at);
        },
        'headerOptions' => [
            'width' => '100px',
        ],
    ],
    [
        'attribute' => 'model',
        'label' => Yii::t('app', 'Model'),
        'content' => function (Log $log) {
            if ($log->is_new) {
                return Yii::t('log', 'Create') . Yii::t('log', $log->model);
            }
            return Yii::t('log', 'Change') . Yii::t('log', $log->model);
        },
    ],
    [
        'attribute' => 'data',
        'label' => Yii::t('app', 'Data'),
        'content' => function (Log $log) {
            $text = '';
            foreach (json_decode($log->data, true) as $attribute => $item) {
                $text .= '<p>' . Yii::t('order', 'log_' . $attribute) . ': ';
                if (!$log->is_new) {
                    $text .=  '<s>' . ($item['old'] ? Yii::t('order', Log::prepareData($attribute, $item['old'])) : Yii::t('order', 'empty')) . '</s> - ';
                }
                $text .=  $item['new'] ? Yii::t('order', Log::prepareData($attribute, $item['new'])) : Yii::t('order', 'empty');
                $text .=  '</p>';
            }
            return $text;
        },
    ],
    [
        'attribute' => 'user_id',
        'label' => Yii::t('app', 'User'),
        'content' => function (Log $log) {
            return $log->user ? $log->user->fio : Yii::t('log', 'System changes');
        },
    ],
    [
        'attribute' => 'user_ip',
        'label' => Yii::t('app', 'User IP'),
    ],
];?>

<p>Заказ #<?=$order->id;?> создан: <?=date('d.m.Y в H:i', $order->created_at);?></p>
<?php Pjax::begin([
    'id' => 'log-form',
    'timeout' => 5000,
    'enablePushState' => false,
]); ?>

<div>
    <h1>История изменений заказа</h1>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions' => function (Log $log) {
            return [];
        },
        'columns' => $columns,
    ]);
    ?>
</div>
<?php Pjax::end(); ?>