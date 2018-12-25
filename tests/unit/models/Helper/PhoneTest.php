<?php
namespace models\Helper;

use app\models\Helper\Date;
use app\models\Helper\Phone;
use Codeception\Test\Unit;
use PHPUnit\Framework\TestResult;

class PhoneTest extends Unit
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
    public function testHumanViewPhone()
    {
        $phone = (new Phone('89260889892'))->getHumanView();
        $this->tester->assertEquals('+7 (926) 088-98-92', $phone, 'Не верный телефон');
    }

    // Тестирование ближайшей даты забора
    public function testClearPhone()
    {
        $phone = (new Phone('+7 (926) 088 98-92'))->getClearPhone();
        $this->tester->assertEquals('9260889892', $phone, 'Не верный телефон');
    }
}