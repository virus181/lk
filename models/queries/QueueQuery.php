<?php

namespace app\models\queries;

use app\models\Rate;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Rate]].
 *
 * @see Shop
 */
class QueueQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Rate[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Rate|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
