<?php declare(strict_types=1);
namespace app\components\Clients\Accounting\Builder;

use app\models\Delivery;
use app\models\Repository\Invoice;
use app\models\Repository\RegistryOrder;
use app\models\Repository\ShopInvoice;
use app\models\sklad\Order;

class Registry
{
    /** @var array */
    private $data;
    /** @var string */
    private $scenario = \app\models\Order::SCENARIO_DEFAULT;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $scenario
     * @return Registry
     */
    public function setScenario(string $scenario): Registry
    {
        $this->scenario = $scenario;
        return $this;
    }

    /**
     *
     * @return \app\models\Repository\Registry
     */
    public function build(): \app\models\Repository\Registry
    {
        $registry = new \app\models\Repository\Registry();

        if (empty($this->data['reestr'])) {
            return $registry;
        }

        $query = \app\models\Repository\Registry::find()->where(['number' => $this->data['reestr']['reest_num']]);
        if ($registryCount = $query->count()) {
            $registry = $query->one();
        }

        $deliveryKey = (new Order())->getShippingKey((int) $this->data['reestr']['delivery']);
        $delivery = (new Delivery())->getDeliveryByCarrierKey($deliveryKey);

        $registry->delivery_id = $delivery ? $delivery->id : 0;
        $registry->number = $this->data['reestr']['reest_num'];
        $registry->name = $this->data['reestr']['reest_name'];
        $registry->created_at = strtotime($this->data['reestr']['reest_date']);
        $registry->updated_at = strtotime($this->data['reestr']['reest_date']);

        if (!empty($this->data['schet'])) {
            if (!$invoice = Invoice::find()->where(['number' => $this->data['schet']['schet_num']])->one()) {
                $invoice = new Invoice();
            }
            $invoice->registry_id = 0;
            $invoice->type = Invoice::TYPE_INVOICE;
            $invoice->number = $this->data['schet']['schet_num'];
            $invoice->status = (int) $this->data['schet']['schet_status'] ?? 0;
            $invoice->sum = $this->data['schet']['schet_summ'];
            $invoice->created_at = strtotime($this->data['schet']['schet_date']);
            $invoice->updated_at = time();

            $invoices[] = $invoice;
        }

        if (!empty($this->data['plat'])) {
            if (!$charge = Invoice::find()->where(['number' => $this->data['plat']['plat_num']])->one()) {
                $charge = new Invoice();
            }
            $charge->registry_id = 0;
            $charge->type = Invoice::TYPE_CHARGE;
            $charge->number = $this->data['plat']['plat_num'];
            $charge->status = (int) $this->data['plat']['plat_status'] ?? 0;
            $charge->sum = $this->data['plat']['plat_summ'];
            $charge->created_at = strtotime($this->data['plat']['plat_date']);
            $charge->updated_at = time();

            $invoices[] = $charge;
        }

        if (!empty($invoices)) {
            $registry->invoices = $invoices;
        }

        $shopIds = [];
        $shopInvoices = [];

        if (!empty($this->data['orders'])) {
            foreach ($this->data['orders'] as $registryOrder) {

                $order = \app\models\Order::findOne((int) $registryOrder['order_id']);
                if (!empty($order) && !in_array($order->shop_id, $shopIds)) {
                    $shopIds[] = $order->shop_id;
                    $sInvoice = new ShopInvoice();
                    $sInvoice->shop_id = $order->shop_id;
                    $order->created_at = time();
                    $order->updated_at = time();
                    $shopInvoices[] = $sInvoice;
                }

                $order = new RegistryOrder();
                $order->order_id = $registryOrder['order_id'];
                $order->total = $registryOrder['order_total_summ'];
                $order->agency_charge = $registryOrder['order_agent'];
                $order->agency_charge_fastery = $registryOrder['order_agent_fast'];
                $order->delivery_cost = $registryOrder['order_dost'];
                $order->fastery_charge = $registryOrder['order_dost_fast'];
                $order->product_cost = $registryOrder['order_summ'];
                $order->created_at = $order->created_at ?? time();
                $order->updated_at = time();

                $orders[] = $order;
            }
            if (!empty($orders)) {
                $registry->orders = $orders;
            }
            if (!empty($shopInvoices)) {
                $registry->shopInvoices = $shopInvoices;
            }
        }


        return $registry;
    }
}
