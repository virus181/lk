<?php

use app\models\User;
use app\widgets\grid\CheckboxColumn;
use app\widgets\grid\ExcelExport;
use app\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
    <?php Pjax::begin(); ?>
    <div class="page-top page-top-sticky">
        <div class="pull-left">
            <?=
            Html::a('<i class="fa fa-plus"></i> ' . Yii::t('app', 'Create User'),
                Url::to(['create']),
                [
                    'class' => 'btn btn-warning btn-sm',
                    'data-href' => Url::to(['create']),
    //                'data-toggle' => 'modal',
    //                'data-target' => '#modal',
                ]
            );
            ?>
        </div>
        <div class="right">
            <!-- <?= Html::a('<i class="fa fa-cog"></i>', '#', ['class' => 'btn btn-default btn-sm']) ?> -->
            <?= ExcelExport::widget([
                'dataProvider' => $exportProvider,
                'columns' => (new \app\models\search\UserSearch())->getExportColumns($searchModel),
            ]) ?>
        </div>
    </div>
    <?=
        GridView::widget([
            'tableOptions' => [
                'data-resizable-columns-id' => 'user',
                'class' => 'table'
            ],
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'rowOptions' => function (User $user) {
                return [
                    'data-href' => Url::to(['view', 'id' => $user->id]),
                    'data-toggle' => 'modal',
                    'data-target' => '#modal',
                ];
            },
            'columns' => (new \app\models\search\UserSearch())->getSearchColumns($searchModel),
        ]);
    ?>
    <?php Pjax::end(); ?>
</div>
