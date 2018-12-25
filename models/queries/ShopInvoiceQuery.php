<?php
namespace app\models\queries;

use app\models\Repository\ShopInvoice;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Account]].
 *
 * @see Shop
 */
class ShopInvoiceQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return ShopInvoice[]
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ShopInvoice|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
