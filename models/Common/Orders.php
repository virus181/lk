<?php declare(strict_types = 1);
namespace app\models\Common;

use Yii;
use yii\base\Model;

class Orders extends Model
{
    /** @var Order[] */
    private $orders;

    /** @var array */
    private $errors;

    /**
     * Устанавливаем заказы
     *
     * @param array $orderIds
     * @return Orders
     */
    public function setOrders(array $orderIds): Orders
    {
        foreach ($orderIds as $orderId) {
            $repository = \app\models\Order::findOne((int) $orderId);
            if (!$repository) {
                continue;
            }
            $this->orders[$orderId] = new Order($repository);
        }

        return $this;
    }

    /**
     * Проверка доступности архивации
     *
     * @return bool
     */
    public function checkArchiveAvailability(): bool
    {
        if (empty($this->orders)) {
            $this->errors[] = Yii::t('order', 'You must choose at least 1 order');
            return false;
        }

        $validate = true;
        foreach ($this->orders as $orderId => $order) {
            if (!$order->isArchiveAvailable()) {
                $this->errors[] = Yii::t('order', 'Order {orderId} can not be archived', ['orderId' => $orderId]);
                $validate = false;
            }
        }

        return $validate;
    }

    /**
     * Множественная архивания заказов
     *
     * @return bool
     */
    public function archive(): bool
    {
        // Запустим архивирование заказаов в транзакции
        $transaction = Yii::$app->db->beginTransaction();

        $isArchived = true;
        foreach ($this->orders as $order) {
            $isUpdated = $order->isArchiveAvailable() && $order->archive();
            if (!$isUpdated) {
                $isArchived = false;
            }
        }

        // Нужно закрыть транзакцию
        if ($isArchived) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }

        return $isArchived;
    }

    /**
     * Множественная разархивация заказов
     *
     * @return bool
     */
    public function unArchive(): bool
    {
        // Запустим архивирование заказаов в транзакции
        $transaction = Yii::$app->db->beginTransaction();

        $isArchived = true;
        foreach ($this->orders as $order) {
            $isUpdated = $order->unArchive();
            if (!$isUpdated) {
                $isArchived = false;
            }
        }

        // Нужно закрыть транзакцию
        if ($isArchived) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }

        return $isArchived;
    }

    /**
     * Получение сообщений об ошибки
     *
     * @return array
     */
    public function getErrorMessages(): ?array
    {
        return $this->errors;
    }
}