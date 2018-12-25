<?php

namespace app\widgets;


class ActiveForm extends \yii\bootstrap\ActiveForm
{
    public $fieldClass = 'app\widgets\ActiveField';

    public $enableClientValidation = false;
}