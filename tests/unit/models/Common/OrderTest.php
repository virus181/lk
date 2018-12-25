<?php
namespace models\Helper;

use app\models\Order;
use Codeception\Test\Unit;

class OrderTest extends Unit
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

    public function getOrderStatuses(): array
    {
        return [
            'created'            => ['OrderWorkflow/created', true],
            'presale'            => ['OrderWorkflow/presale', true],
            'inCollecting'       => ['OrderWorkflow/inCollecting', true],
            'confirmed'          => ['OrderWorkflow/confirmed', true],
            'redyForDelivery'    => ['OrderWorkflow/redyForDelivery', true],
            'waitingCourier'     => ['OrderWorkflow/waitingCourier', false],
            'inDelivery'         => ['OrderWorkflow/inDelivery', false],
            'delivered'          => ['OrderWorkflow/delivered', true],
            'partiallyDelivered' => ['OrderWorkflow/partiallyDelivered', false],
            'canceledAtDelivery' => ['OrderWorkflow/canceledAtDelivery', true],
            'readyDelivery'      => ['OrderWorkflow/readyDelivery', false],
            'onReturn'           => ['OrderWorkflow/onReturn', false],
            'returned'           => ['OrderWorkflow/returned', true],
        ];
    }

    /**
     * @dataProvider getOrderStatuses
     */
    public function testCheckAvailableArchiveForOrder($status, $result)
    {
        $repository         = new Order();
        $repository->status = $status;

        $order     = new \app\models\Common\Order($repository);
        $isVaidate = $order->isArchiveAvailable();

        $this->tester->assertEquals($result, $isVaidate, 'Проверка на доступность возможности архивации заказа');
    }


    public function testCheckArchiveOrder()
    {
        $this->markTestIncomplete('Написать тест для проверки');
    }


    public function testCheckUnArchiveOrder()
    {
        $this->markTestIncomplete('Написать тест для проверки');
    }
}