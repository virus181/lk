<?php
/**
 * Created by PhpStorm.
 * User: avsosnovskiy
 * Date: 12.04.17
 * Time: 16:57
 */

namespace app\models\search;


use yii\data\ActiveDataProvider;

interface SearchModelInterface
{
    /**
     * @param $params array
     * @return ActiveDataProvider
     */
    public function search($params);
}