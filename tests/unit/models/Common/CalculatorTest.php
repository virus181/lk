<?php
namespace models\Helper;

use app\models\Common\Calculator;
use app\models\Helper\Date;
use Codeception\Test\Unit;
use PHPUnit\Framework\TestResult;

class CalculatorTest extends Unit
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

    public function getCalculatorDate(): array
    {
        return [
            'Empty request' => [[], false]
        ];
    }

    /**
     * @dataProvider getCalculatorDate
     */
    public function testValidateCalculator($request, $result)
    {
        $calculator = new Calculator();
        $calculator->load($request);
        $calculator->prepareData();
        $isVaidate = $calculator->validate();

        $this->tester->assertEquals($result, $isVaidate, 'Валидатор калькулятора не правильно валидирует');
    }
}