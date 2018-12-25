<?php declare(strict_types=1);
namespace app\models\Repository;

use app\models\queries\ShopInvoiceQuery;
use app\models\Shop;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_invoice}}".
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $shop_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Registry $registry
 * @property Shop $shop
 * @property RegistryOrder[] $orders
 */
class ShopInvoice extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_invoice}}';
    }

    /**
     * @inheritdoc
     * @return ShopInvoiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopInvoiceQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'invoice_id', 'shop_id', 'created_at', 'updated_at'], 'integer'],
            [['invoice_id', 'shop_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }
}
