<?php
namespace app\models\Helper;

class Date
{
    const BORDER_TIME = '09:00:00';
    const DAY = 86400;

    /** @var int */
    private $time;

    /** @var string  */
    private $format = 'timestamp';

    /**
     * @param int $time
     */
    public function __construct(int $time)
    {
        $this->time = $time;
    }

    /**
     * @param string $format
     * @return Date
     */
    public function setFormat(string $format): Date
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Получить отформатированную дату
     * @param int $time
     * @return string|int
     */
    private function getFormattedDate(int $time)
    {
        if ($this->format == 'timestamp') {
            return $time;
        }

        return date($this->format, $time);
    }

    /**
     * Получить время до которого можно сегодня подтвердить заказ на забор
     * @return int
     */
    private function getBorderTime(): int
    {
        return strtotime(date('Y-m-d', $this->time) . ' ' . self::BORDER_TIME);
    }

    /**
     * Можно ли отправить заказ сегодня
     * @param int $processDayCount
     * @return bool
     */
    private function canPickupToday(int $processDayCount): bool
    {
        $nowTime = $this->time;
        $borderTime = $this->getBorderTime();
        return $borderTime > ($nowTime + $processDayCount * self::DAY);
    }

    /**
     * Получить ближайшую дату забора заказа
     * @param int $processDayCount
     * @return int|string
     */
    public function getNearestPickupDate(int $processDayCount = 0)
    {
        $pickupTime = $this->time + $processDayCount * self::DAY;
        if ($this->canPickupToday($processDayCount)) {
            return $this->getFormattedDate($pickupTime);
        }
        $dayOfWeek = date("N", $pickupTime = $pickupTime + self::DAY);

        $pickupTime = ($dayOfWeek > 5) ? ($pickupTime + (8 - $dayOfWeek) * 86400): $pickupTime;
        return $this->getFormattedDate($pickupTime);
    }

    /**
     * Получить ближайшую дату доставки
     *
     * @param int $minTerm
     * @param int $deliveryTime
     * @return int|string
     */
    public function getNearestDeliveryDate(int $minTerm = 0, int $deliveryTime = 0)
    {
        $nearestDeliveryTime = $this->time + $minTerm * self::DAY;
        return $this->getFormattedDate($nearestDeliveryTime > $deliveryTime
            ? $nearestDeliveryTime
            : $deliveryTime
        );
    }

    /**
     * Получение времени
     *
     * @param int $hour
     * @param string $time
     * @return int|string
     */
    public function getTime(int $hour, string $time = '')
    {
        $date = $time ? $time : sprintf('%d:00:00', $hour);
        return $this->getFormattedDate(strtotime($date));
    }
}