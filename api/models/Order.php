<?php

namespace app\api\models;

use Yii;
use yii\base\Model;

class Order extends Model
{
    public $order;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['order'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order' => Yii::t('app', 'Order'),
        ];
    }
}