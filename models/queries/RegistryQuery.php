<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\Registry]].
 *
 * @see \app\models\Courier
 */
class RegistryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \app\models\Courier[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\Courier|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
