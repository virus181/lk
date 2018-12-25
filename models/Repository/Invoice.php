<?php declare(strict_types=1);
namespace app\models\Repository;

use app\models\Helper\Status;
use app\models\queries\InvoiceQuery;
use app\models\Shop;
use app\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%invoice}}".
 *
 * @property int $id
 * @property int $registry_id
 * @property int $type
 * @property string $number
 * @property integer $status
 * @property float $sum
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Registry $registry
 * @property Shop[] $shops
 * @property RegistryOrder[] $orders
 */
class Invoice extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_IN_ACTIVE = 0;

    const TYPE_INVOICE = 1;
    const TYPE_CHARGE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice}}';
    }

    /**
     * @inheritdoc
     * @return InvoiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InvoiceQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'registry_id', 'type', 'created_at', 'updated_at', 'status'], 'integer'],
            [['sum'], 'double'],
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_IN_ACTIVE,
            ]],
            [['number'], 'string', 'max' => 255],
            [['number'], 'unique', 'targetClass' => Invoice::className()],
            [['number', 'sum', 'status'], 'required'],
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
     * @param int $id
     * @param User $user
     * @return Invoice|null
     */
    public function findOwn(int $id, User $user): ?Invoice
    {
        return Invoice::find()
            ->joinWith(['shops'])
            ->where(['invoice.id' => $id])
            ->andFilterWhere(['shop.id' => $user->getAllowedShopIds()])
            ->one();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('shop', 'ID'),
            'registry_id' => Yii::t('shop', 'Registry ID'),
            'type'        => Yii::t('shop', 'Invoice TYPE'),
            'number'      => Yii::t('shop', 'Registry number'),
            'sum'         => Yii::t('shop', 'Summ'),
            'status'      => Yii::t('shop', 'Status'),
            'created_at'  => Yii::t('shop', 'Created At'),
            'updated_at'  => Yii::t('shop', 'Updated At'),
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
    public function getShops()
    {
        return $this->hasMany(Shop::className(), ['id' => 'shop_id'])->viaTable('{{%shop_invoice}}', ['invoice_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(RegistryOrder::className(), ['invoice_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getDocumentDate(): string
    {
        return date('Y-m-d, H:i', $this->created_at);
    }

    /**
     * @param bool $formatted
     * @return string
     */
    public function getDocumentSum($formatted = true): string
    {
        return $formatted ? Yii::$app->formatter->asCurrency($this->getSum(), 'RUB') : (string) $this->getSum();
    }

    /**
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * @return string
     */
    public function getDocumentNumber(): string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getDocumentShopName(): string
    {
        $shops = [];
        foreach ($this->shops as $shop) {
            $shops[] = $shop->name;
        }
        return implode(', ', $shops);
    }

    /**
     * @return string
     */
    public function getDocumentType(): string
    {
        switch ($this->type) {
            case self::TYPE_INVOICE:
                return Yii::t('shop', 'Invoice');
            case self::TYPE_CHARGE:
                return Yii::t('shop', 'Charge');
            default:
                return Yii::t('shop', 'Unknown');
        }
    }

    /**
     * @return int
     */
    public function getOrderCount(): int
    {
        return !empty($this->orders) ? count($this->orders) : 0;
    }

    /**
     * @return string
     */
    public function getRegistryNumber(): string
    {
        return $this->registry->number;
    }

    /**
     * @return string
     */
    public function getDocumentStatus(): string
    {
        return ArrayHelper::getValue( (new Status())->getStatusList(), $this->status);
    }
}
