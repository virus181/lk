<?php declare(strict_types = 1);
namespace app\models\Common;

use app\models\Product;
use Yii;
use yii\base\Model;

class Order extends Model
{
    /** @var \app\models\Order */
    private $order;

    /**
     * @param \app\models\Order $order
     * @param array $config
     */
    public function __construct(\app\models\Order $order, array $config = [])
    {
        $this->order = $order;
        parent::__construct($config);
    }

    /**
     * Архивирует заказ
     *
     * @return bool
     */
    public function archive(): bool
    {
        $this->order->is_archive = true;
        if ($this->order->validate()) {
            return $this->order->save();
        }

        return false;
    }

    /**
     * Разархивирует заказ
     *
     * @return bool
     */
    public function unArchive(): bool
    {
        $this->order->is_archive = false;
        if ($this->order->validate()) {
            return $this->order->save();
        }

        return false;
    }

    /**
     * Проверка на доступность архивирования заказа
     *
     * @return bool
     */
    public function isArchiveAvailable(): bool
    {
        return in_array($this->order->status, $this->order::STATUS_ARCHIVABLE);
    }

    /**
     * @param Product[] $products
     * @return bool
     */
    public function validateConsistentlyProducts(array $products): bool
    {
        $barCodes = [];
        $validate = true;
        foreach ($products as $key => $product) {
            if (in_array($product->barcode . "_" . (string) $product->id, $barCodes)) {
                $products[$key]->addError(
                    'barcode',
                    Yii::t('product', 'Product must be unique')
                );
                $validate = false;
            } else {
                $barCodes[] = $product->barcode . "_" . (string) $product->id;
            }
        }
        return $validate;
    }

    /**
     * Получение реальной стоимости доставки
     *
     * @return float
     */
    public function getActualDeliveryCost(): float
    {
        $cost = 0;
        if (!empty($this->order->registryOrders)) {
            foreach ($this->order->registryOrders as $registryOrder) {
                $cost += (new RegistryOrder($registryOrder))->getActualDeliveryCost();
            }
        }
        return $cost;
    }
}