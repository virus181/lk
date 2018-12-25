<?php declare(strict_types=1);
namespace app\models\Repository;

use app\models\Order;
use app\models\queries\CommonQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%registry_order}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $registry_id
 * @property int $invoice_id
 * @property float $total
 * @property float $product_cost
 * @property float $agency_charge
 * @property float $agency_charge_fastery
 * @property float $delivery_cost
 * @property float $fastery_charge
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Order $order
 */
class RegistryOrder extends ActiveRecord
{
    const TYPE_CHARGE = 1;
    const TYPE_INVOICE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%registry_order}}';
    }

    /**
     * @inheritdoc
     * @return CommonQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommonQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'registry_id', 'invoice_id', 'created_at', 'updated_at'], 'integer'],
            [['total', 'product_cost', 'agency_charge', 'agency_charge_fastery', 'delivery_cost', 'fastery_charge'], 'double'],
            [['order_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => Yii::t('registry', 'ID'),
            'order_id'              => Yii::t('registry', 'Order ID'),
            'registry_id'           => Yii::t('registry', 'Registry ID'),
            'invoice_id'            => Yii::t('registry', 'Invoice ID'),
            'total'                 => Yii::t('registry', 'Total cost'),
            'product_cost'          => Yii::t('registry', 'Product cost'),
            'agency_charge'         => Yii::t('registry', 'Agency charge cost'),
            'agency_charge_fastery' => Yii::t('registry', 'Fastery agency charge cost'),
            'delivery_cost'         => Yii::t('registry', 'Delivery cost'),
            'fastery_charge'        => Yii::t('registry', 'Fastery charge'),
            'created_at'            => Yii::t('registry', 'Created At'),
            'updated_at'            => Yii::t('registry', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegistry()
    {
        return $this->hasOne(Registry::className(), ['id' => 'registry_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'invoice_id']);
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->order_id;
    }

    /**
     * @return float
     */
    public function getProductCost(): float
    {
        return $this->product_cost;
    }

    /**
     * @return float
     */
    public function getDeliveryCost(): float
    {
        return $this->delivery_cost;
    }
}
