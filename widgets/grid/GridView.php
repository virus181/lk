<?php
namespace app\widgets\grid;

class GridView extends \yii\grid\GridView
{
    public $tableOptions = ['class' => 'table'];

    public $dataColumnClass = 'app\widgets\grid\DataColumn';

    public $emptyTextOptions = ['class' => 'empty text-center'];

    public $pager = ['class' => 'app\widgets\LinkPager'];

    public $layout = "{items}\n{pager}";

    public $summaryOptions = ['class' => 'summary pull-right'];

//    public $filterPosition = self::FILTER_POS_HEADER;
}