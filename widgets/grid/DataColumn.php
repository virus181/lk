<?php
namespace app\widgets\grid;

class DataColumn extends \yii\grid\DataColumn
{
    public $filterInputOptions = ['class' => 'form-control input-xs', 'autocomplete' => null];
    public $contentOptions = ['style' => 'overflow: hidden'];
}