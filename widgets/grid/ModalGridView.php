<?php
namespace app\widgets\grid;

class ModalGridView extends \yii\grid\GridView
{
    public $tableOptions = ['class' => 'table'];

    public $dataColumnClass = 'app\widgets\grid\DataColumn';

    public $emptyTextOptions = ['class' => 'empty text-center'];

    public $sorter = false;

    public $layout = "{items}";

    public $showHeader = true;

    public $summaryOptions = ['class' => 'summary pull-right'];

//    public $filterPosition = self::FILTER_POS_HEADER;
}