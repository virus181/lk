<?php declare(strict_types=1);
namespace app\models\Common;

use app\models\Helper\Params;
use app\models\OrderProduct;
use app\models\Product;
use yii\base\Model;

class Products extends Model
{
    /**
     * @param array $loadData
     * @return OrderProduct[]
     */
    public function getXMLProducts(array $loadData): array
    {
        foreach ($loadData as $key => $data) {
            $param   = new Params();
            $product = new OrderProduct();

            $product->product_id     = $param->getArrayParam('object', 0, $data);
            $product->name           = $param->getArrayParam('string', 2, $data);
            $product->weight         = $param->getArrayParam('float', 3, $data) * 1000;
            $product->quantity       = $param->getArrayParam('int', 4, $data);
            $product->price          = $param->getArrayParam('int', 5, $data);
            $product->accessed_price = $param->getArrayParam('int', 6, $data);

            $mainProduct = new Product();

            $mainProduct->shop_id        = 0;
            $mainProduct->additional_id  = null;
            $mainProduct->barcode        = $param->getArrayParam('string', 1, $data);
            $mainProduct->count          = $param->getArrayParam('int', 4, $data);
            $mainProduct->weight         = $param->getArrayParam('float', 3, $data) * 1000;
            $mainProduct->quantity       = $param->getArrayParam('int', 4, $data);
            $mainProduct->name           = $param->getArrayParam('string', 2, $data);
            $mainProduct->price          = $param->getArrayParam('int', 5, $data);
            $mainProduct->accessed_price = $param->getArrayParam('int', 6, $data);

            $product->product = $mainProduct;
            $products[$key]   = $product;
        }

        return $products ?? [];
    }
}