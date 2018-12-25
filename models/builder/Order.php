<?php
namespace app\models\builder;

use app\models\Address;
use app\models\Product;
use Yii;

class Order
{
    /** @var array */
    private $post;
    /** @var string */
    private $scenario = \app\models\Order::SCENARIO_DEFAULT;

    /**
     * @param array $post
     */
    public function __construct(array $post)
    {
        $this->post = $post;
    }

    /**
     * @param string $scenario
     * @return Order
     */
    public function setScenario(string $scenario): Order
    {
        $this->scenario = $scenario;
        return $this;
    }

    /**
     * @return \app\models\Order
     */
    public function build(): \app\models\Order
    {
        $order = new \app\models\Order();
        $order->scenario = $this->scenario;

        if (empty($this->post)) {
            return $order;
        }

        /** @var Product[] $products */
        $products = [new Product()];

        foreach (Yii::$app->request->post('Product', []) as $i => $productRequest) {
            $product = new Product();
            $products[$i] = $product;
        }

        Product::loadMultiple($products, $this->post);

        foreach ($products as $product) {
            if ($product->weight !== '') {
                $product->weight = ((float) str_replace(',', '.', $product->weight)) * 1000;
            }
        }

        $order->products = $products;
        $order->weight = $order->getWeight();
        $order->address = new Address();

        $order->address->load($this->post);
        $order->load($this->post);

        return $order;
    }
}
