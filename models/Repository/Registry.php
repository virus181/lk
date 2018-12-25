<?php declare(strict_types=1);
namespace app\models\Repository;

use app\models\Delivery;
use app\models\Order;
use app\models\queries\CommonQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%registry}}".
 *
 * @property int $id
 * @property string $number
 * @property string $name
 * @property int $delivery_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 *
 * @property Invoice[] $invoices
 * @property RegistryOrder[] $orders
 * @property ShopInvoice[] $shopInvoices
 */
class Registry extends ActiveRecord
{
    /** @var array */
    protected $_errors;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%registry}}';
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
            [['id', 'delivery_id', 'created_at', 'updated_at', 'status'], 'integer'],
            [['number', 'name'], 'string', 'max' => 255],
            [['number', 'name'], 'string', 'max' => 255],
            [['number'], 'unique', 'targetClass' => Registry::className()],
            [['delivery_id'], 'exist', 'skipOnError' => false, 'targetClass' => Delivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
            [['delivery_id', 'number', 'name'], 'required'],
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
            'id'         => Yii::t('registry', 'ID'),
            'delivery'   => Yii::t('registry', 'Delivery service ID'),
            'name'       => Yii::t('registry', 'Name'),
            'number'     => Yii::t('registry', 'Registry number'),
            'status'     => Yii::t('registry', 'Status'),
            'created_at' => Yii::t('registry', 'Created At'),
            'updated_at' => Yii::t('registry', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoice::className(), ['registry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery()
    {
        return $this->hasOne(Delivery::className(), ['id' => 'delivery_id']);
    }

    /**
     * @param Invoice[] $invoices
     */
    public function setInvoices(array $invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * @param ShopInvoice[] $shopInvoices
     */
    public function setShopInvoices(array $shopInvoices)
    {
        $this->shopInvoices = $shopInvoices;
    }

    /**
     * @param RegistryOrder[] $orders
     */
    public function setOrders(array $orders)
    {
        $this->orders = $orders;
    }

    /**
     * @param array $error
     * @return array
     */
    public function setErrors(array $error): array
    {
        $this->_errors = array_merge($this->_errors ?? [], $error);
        return $this->_errors ?? [];
    }

    /**
     * @return array
     */
    public function getErrorsAll(): array
    {
        return $this->_errors ?? [];
    }

    /**
     * Валидировать модель со всеми дочерними моделями
     *
     * @return bool
     */
    public function validateAll(): bool
    {
        $registryValidate = $this->validate();

        if (!$registryValidate) {
            $this->setErrors($this->errors);
        }

        if (empty($this->invoices)) {
            $this->setErrors([
                'shopInvoices' => [
                    Yii::t('registry', 'Object is missing')
                ]
            ]);
        }

        if (empty($this->orders)) {
            $this->setErrors([
                'orders' => [
                    Yii::t('registry', 'Object is missing')
                ]
            ]);
            $registryValidate = false;
        }

        $invoiceValidate = $this->validateSubModels($this->invoices);
        $orderValidate = $this->validateSubModels($this->orders);

        return $registryValidate && $invoiceValidate && $orderValidate;
    }

    /**
     * @param ActiveRecord[] $models
     * @return bool
     */
    public function validateSubModels(array $models): bool
    {
        $validate = true;

        if (!empty($models)) {
            foreach ($models as $model) {
                if (!$model->validate()) {
                    $validate = false;
                    $errors[] = $model->errors;
                }
            }
            if (!empty($errors)) {
                $this->setErrors([
                    get_class(current($models)) => $errors
                ]);
            }
        }

        return $validate;
    }

    /**
     * Сохранить модель со всеми дочерними моделями
     *
     * @return bool
     */
    public function saveAll(): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        if (!$this->save()) {
            $transaction->rollBack();
            return false;
        }

        $invoiceId = null;
        foreach ($this->invoices as $invoice) {
            $invoice->registry_id = $this->id;
            if (!$invoice->save()) {
                $transaction->rollBack();
                return false;
            }
            $invoiceId = $invoice->id;
        }

        foreach ($this->shopInvoices as $shopInvoice) {
            $shopInvoice->invoice_id = $invoiceId;
            if (!$shopInvoice->save()) {
                $transaction->rollBack();
                return false;
            }
        }

        foreach ($this->orders as $order) {
            $order->registry_id = $this->id;
            $order->invoice_id = $invoiceId;
            if (!$order->save()) {
                $transaction->rollBack();
                return false;
            }
        }

        $transaction->commit();
        return true;
    }
}
