<?php
namespace models\Helper;

use app\models\Helper\Date;
use Codeception\Test\Unit;
use PHPUnit\Framework\TestResult;

class DateTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // Тестирование ближайшей даты забора
    public function testNearestPickupDate()
    {
        $time = strtotime('2018-09-21 18:09:00');
        $date = (new Date($time))
            ->setFormat('d.m.Y')
            ->getNearestPickupDate();
        $this->tester->assertEquals('24.09.2018', $date, 'Не верная дата забора');
    }


    // Тестирование ближайшей даты забора
    public function testTodayPickupDate()
    {
        $time = strtotime('2018-09-21 07:39:00');
        $date = (new Date($time))
            ->setFormat('d.m.Y')
            ->getNearestPickupDate();
        $this->tester->assertEquals('21.09.2018', $date, 'Не верная дата забора');
    }

    // Тестирование ближайшей даты доставки
    public function testNearestDeliveryDate()
    {
        $time = strtotime('2018-09-21 18:09:00');
        $pickupDate = (new Date($time))->getNearestPickupDate();
        $minTerm = 3;
        $date = (new Date($pickupDate))->setFormat('d.m.Y')->getNearestDeliveryDate($minTerm);
        $this->tester->assertEquals('27.09.2018', $date, 'Не верная дата доставки');
    }

    // Тестирование ближайшей даты доставки если забор сегодня
    public function testNearestDeliveryDateWithTodayPickup()
    {
        $time = strtotime('2018-09-21 07:59:00');
        $pickupDate = (new Date($time))->getNearestPickupDate();
        $minTerm = 3;
        $date = (new Date($pickupDate))->setFormat('d.m.Y')->getNearestDeliveryDate($minTerm);
        $this->tester->assertEquals('24.09.2018', $date, 'Не верная дата доставки');
    }

    // Тестирование ближайшей даты доставки если дата доставки была выбрана ранее
    public function testNearestDeliveryDateIfDeliveryDateSetted()
    {
        $time = strtotime('2018-09-21 17:59:00');
        $pickupDate = (new Date($time))->getNearestPickupDate();
        $minTerm = 3;
        $date = (new Date($pickupDate))->setFormat('d.m.Y')->getNearestDeliveryDate($minTerm, strtotime('2018-09-29'));
        $this->tester->assertEquals('29.09.2018', $date, 'Не верная дата доставки');
    }

    // Тестирование ближайшей даты доставки если дата доставки была выбрана ранее и она не доступна
    public function testNearestDeliveryDateIfDeliveryDateSettedNotAvailable()
    {
        $time = strtotime('2018-09-21 17:59:00');
        $pickupDate = (new Date($time))->getNearestPickupDate();
        $minTerm = 1;
        $date = (new Date($pickupDate))->setFormat('d.m.Y')->getNearestDeliveryDate($minTerm, strtotime('2018-09-23'));
        $this->tester->assertEquals('25.09.2018', $date, 'Не верная дата доставки');
    }

    // Тестирование получение времени
    public function testTime()
    {
        $time = strtotime('2018-09-21 17:59:00');
        $date = (new Date($time))->setFormat('H:i:s')->getTime(10);
        $this->tester->assertEquals('10:00:00', $date, 'Не верное время');
    }

    // Тестирование получение времени из формы
    public function testTimeAndTimeIsSetted()
    {
        $time = strtotime('2018-09-21 17:59:00');
        $date = (new Date($time))->setFormat('H:i:s')->getTime(10, '09:00');
        $this->tester->assertEquals('09:00:00', $date, 'Не верное время');
    }
}