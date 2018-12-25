<?php
namespace app\models\queries;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\DeliveryStatus]].
 *
 * @see \app\models\DeliveryStatus
 */
class DeliveryStatusQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \app\models\DeliveryStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\DeliveryStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
