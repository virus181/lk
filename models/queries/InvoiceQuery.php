<?php
namespace app\models\queries;

use app\models\Repository\Invoice;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Account]].
 *
 * @see Shop
 */
class InvoiceQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return Invoice[]
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Invoice|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
