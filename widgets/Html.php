<?php

namespace app\widgets;


class Html extends \yii\bootstrap\Html
{
    /**
     * @param string $type
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param array $options
     * @return string
     */
    public static function activeInput($type, $model, $attribute, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
        $value = isset($options['value']) ? $options['value'] : static::getAttributeValue($model, $attribute);
        if (!array_key_exists('id', $options)) {
            $options['id'] = static::getInputId($model, $attribute);
        }

        $rawName = $attribute;

        if (strpos($attribute, ']')) {
            $finds = strpos($attribute, ']') + 1;
            $rawName = substr($attribute, $finds);
        }

        if ($model->hasErrors($rawName)) {
            $options['data-toggle'] = 'tooltip-error';
            $options['data-title'] = $model->getFirstError($rawName);
        }

        return static::input($type, $name, $value, $options);
    }
}