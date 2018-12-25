<?php
namespace app\models\Common;

use yii\base\Model;

class Registry extends Model
{
    const LAST_DATE = '2018-11-01 00:00:00';

    /**
     * Дата последнего обновленного реестра
     *
     * @return int
     */
    public function getRegistryLastUpdatedTime(): int
    {
        if (!$registry = \app\models\Repository\Registry::find()->orderBy(['updated_at' => SORT_DESC])->one()) {
            return strtotime(self::LAST_DATE);
        }

        return $registry->updated_at;
    }
}