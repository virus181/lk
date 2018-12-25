<?php

namespace app\rbac;

use yii\base\Action;
use yii\base\Event;
use yii\db\BaseActiveRecord;

class ControllerAccessEvent extends Event
{
    const AFTER_FIND_MODEL = 'afterFindModel';
    const AFTER_CHECK_ACCESS = 'checkAccess';
    /** @var  Action */
    public $action;
    /** @var BaseActiveRecord */
    public $model;
}