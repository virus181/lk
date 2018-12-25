<?php
namespace app\api\models;

use Yii;
use yii\base\Model;

class Tracker extends Model
{
    public $number;
    public $callback;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['callback', 'number'], 'required'],
            [['number'], 'integer'],
            [['callback'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'number' => Yii::t('app', 'Number'),
            'callback' => Yii::t('app', 'Callback'),
        ];
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getCallback(): string
    {
        return $this->callback;
    }
}