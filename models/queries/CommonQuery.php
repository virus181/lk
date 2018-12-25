<?php
namespace app\models\queries;

use app\models\Rate;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Rate]].
 *
 * @see Shop
 */
class CommonQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return CommonQuery[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CommonQuery|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
