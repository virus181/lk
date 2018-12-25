<?php

namespace app\components;

use Yii;
use yii\web\HttpException;

class UserException extends HttpException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return Yii::t('app', 'Error');
    }
}