<?php
namespace app\models\queries;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\DeliveryService]].
 *
 * @see \app\models\DeliveryService
 */
class DeliveryServiceQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \app\models\DeliveryService[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\DeliveryService|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
