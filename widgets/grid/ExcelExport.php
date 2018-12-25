<?php
namespace app\widgets\grid;

use kartik\export\ExportMenu;

class ExcelExport extends ExportMenu
{
    public $showConfirmAlert = false;
    public $deleteAfterSave = true;
    public $asDropdown = false;
    public $showColumnSelector = false;
    public $fontAwesome = true;
    public $dropdownOptions = ['class' => 'btn btn-sm btn-default', 'icon' => '<i class="fa fa-file-excel-o"></i>'];
    public $exportFormView =  '@app/vendor/kartik-v/yii2-export/views/_form';
    public $folder = '@webroot/temp';

    public $exportConfig = [
        ExportMenu::FORMAT_EXCEL_X => [
            'label' => false,
            'linkOptions' => ['tag' => 'button', 'class' => 'btn btn-sm btn-default'],
            'iconOptions' => false,
            'options' => ['tag' => false],
        ],
        ExportMenu::FORMAT_CSV => false,
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_HTML => false,
        ExportMenu::FORMAT_TEXT => false,
        ExportMenu::FORMAT_EXCEL => false,
    ];
}