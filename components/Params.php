<?php

namespace app\components;

use yii\base\Component;

class Params extends Component
{
    /**
     * Формирование актов BoxBerry
     * @param $params
     */
    public function getContextValues($params)
    {
        $param = '<div id="data-param-element"';
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $param .= ' data-'.$key.'="'.$value.'"';
        }
        $param .= '></div>';

        echo $param;
    }
}