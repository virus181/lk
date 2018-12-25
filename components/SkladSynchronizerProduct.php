<?php
namespace app\components;

use app\models\Delivery;
use app\models\OrderDelivery;
use app\models\Product;
use app\models\Shop;
use app\models\sklad\Market;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\log\Logger;

class SkladSynchronizerProduct extends Component
{
    /** @var Connection|string */
    public $db = 'db_sklad';
    private $errorMessages = [];

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, 'yii\db\Connection');
    }

    /**
     *
     */
    public function sinchronize()
    {
        echo 'Начинаем синхронизировать магазины и продукты: \n';

        /** @var Market $market */
//        $markets = Market::find()->with(['catalogs', 'catalogs.product']);
        foreach (Market::find()->with(['catalogs', 'catalogs.product'])->each(400) as $market) {

            echo $market['Name'] . "\n";

            if (($shop = Shop::find()->where(['additional_id' => $market->SN])->one()) === null) {

                echo ' - Магазин не найден, настройте маназин в ЛК' . "\n";
                // $this->errorMessages[] = 'Магазин '.$market->SN.' не найден, настройте маназин в ЛК';
                continue;

                /*
                $shop = new Shop();
                $shop->name = $market->Name;
                $shop->additional_id = $market->SN;
                $shop->fulfillment = true;
                $shop->process_day = Shop::DEFAULT_PROCCESS_DAY_COUNT;
                $shop->default_warehouse_id = Shop::DEFAULT_WAREHOUSE_ID;

                $url = $market->WebSite;
                $urlArr = explode(' ', $url);
                if ($urlArr) {
                    $url = $urlArr[0];
                }

                $url = str_replace('www.', '', $url);

                if (strpos($url, 'http') !== 0 && $url !== '') {
                    $url = 'http://' . $url;
                }

                $shop->url = $url;

                if (!$shop->save()) {
                    $this->errorMessages[] = print_r($shop->errors, true);
                    echo "Shop does not save. Error: \n" . print_r($shop->errors, true);
                    Yii::error("Shop does not save. Error: \n" . print_r($shop->errors, true), __METHOD__);
                    continue;
                }

                $shop_deliveries = [];
                foreach (Delivery::find()->asArray()->all() as $delivery) {
                    if ($delivery['status']) {
                        $shop_deliveries[] = [$shop->id, $delivery['id']];
                    }
                }
                (new Query())->createCommand()->batchInsert('{{%shop_delivery}}', ['shop_id', 'delivery_id'], $shop_deliveries)->execute();

                $shop_types = [
                    [$shop->id, OrderDelivery::DELIVERY_TO_DOOR],
                    [$shop->id, OrderDelivery::DELIVERY_TO_POINT],
                    [$shop->id, OrderDelivery::DELIVERY_POST]
                ];
                (new Query())->createCommand()->batchInsert('{{%shop_type}}', ['shop_id', 'type'], $shop_types)->execute();
                */
            }

            if ($shop->status == Shop::STATUS_DELETED) {
                echo ' - Магазин деактивирован' . "\n";
                continue;
            }

            foreach ($market->catalogs as $catalog) {
                if (
                    ($product = Product::find()
                        ->where([
                            'additional_id' => $catalog->SN,
                            'status' => Product::STATUS_ACTIVE
                        ])
                        ->one()
                    ) === null
                    && ($product = Product::find()
                        ->where([
                            'barcode' => $catalog->Article,
                            'shop_id' => $shop->id,
                            'status' => Product::STATUS_ACTIVE
                        ])
                        ->one()
                    ) === null
                ) {
                    $product = new Product();
                    $product->shop_id = $shop->id;
                }

                // Если нет цены то мы не будем синхронизировать данный продукт
                if (!$catalog->Price) {
                    continue;
                }

                $product->name = $catalog->product->Name;
                $product->barcode = $catalog->Article;
                $product->additional_id = $catalog->SN;
                $product->weight = $catalog->Weight * 1000;
                $product->count = $catalog->Exist;
                $product->price = $catalog->Price;
                $product->accessed_price = $catalog->Price;

                if (!$product->save()) {
                    $this->errorMessages[] = print_r($product->errors, true);
                    echo "Product does not save. Error: \n" . print_r($product->errors, true);
                    Yii::error("Product does not save. Error: \n" . print_r($product->errors, true), __METHOD__);
                }
            }
        }

        if (!empty($this->errorMessages)) {
            Yii::$app->slack->send('Synchronize product error', ':thumbs_up:', [
                [
                    'fallback' => 'Log message',
                    'color' => Yii::$app->slack->getLevelColor(Logger::LEVEL_ERROR),
                    'fields' => [
                        [
                            'title' => 'Application ID',
                            'value' => Yii::$app->id,
                            'short' => true,
                        ],
                        [
                            'title' => 'Error',
                            'value' => implode('; ', $this->errorMessages),
                            'short' => true,
                        ]
                    ],
                ],
            ]);
        }
    }

    /**
     * Синхронизация товаров со складской системой
     */
    public function sinchronizeV2()
    {
        echo "Начинаем синхронизировать магазины и продукты V2: \n";

        try {
            $shops = Shop::find()
                ->select(['id', 'additional_id', 'name'])
                ->where(['status' => Shop::STATUS_ACTIVE])
                ->andWhere(['IS NOT', 'additional_id', null])
                ->asArray()
                ->all();

            foreach ($shops as $key => $shop) {

//                if ($shop['additional_id'] != '3225') {
//                    continue;
//                }

                /** @var Market $market */
                $market = Market::find()
                    ->with(['catalogs', 'catalogs.product'])
                    ->where(['SN' => $shop['additional_id']])
                    ->one();

                if (empty($market->catalogs)) {
                    continue;
                }

                $updatedProductCount = 0;
                foreach ($market->catalogs as $catalog) {

                    // Если нет цены то мы не будем синхронизировать данный продукт
                    if (!$catalog->Price) {
                         continue;
                    }

                    if (
                        ($product = Product::find()
                            ->where([
                                'additional_id' => $catalog->SN,
                                'status' => Product::STATUS_ACTIVE
                            ])
                            ->one()
                        ) === null
                        && ($product = Product::find()
                            ->where([
                                'barcode' => $catalog->Article,
                                'shop_id' => $shop['id'],
                                'status' => Product::STATUS_ACTIVE
                            ])
                            ->one()
                        ) === null
                    ) {
                        $product = new Product();
                        $product->shop_id = $shop['id'];
                    }
//
//                    if ($shop['additional_id'] == '3225') {
//                        echo "Товар " .$product->name. " - Артикул: ".$catalog->Article.", Количество: ".$catalog->Exist." \n";
//                    }

                    $product->name = $catalog->product ? $catalog->product->Name : 'Товар с артикулом: ' . $catalog->Article;
                    $product->barcode = $catalog->Article;
                    $product->additional_id = $catalog->SN;
                    $product->weight = $catalog->Weight * 1000;
                    $product->count = $catalog->Exist;
                    $product->price = $catalog->Price;
                    $product->accessed_price = $catalog->Price;

                    if (!$product->save()) {
                        $this->errorMessages[] = print_r($product->errors, true);
                        echo "Product does not save. Error: \n" . print_r($product->errors, true);
                        Yii::error(
                            "Product does not save. Error: \n" . print_r($product->errors, true),
                            __METHOD__
                        );
                    }



                    $updatedProductCount++;
                }

                echo $key . ")" . $shop['name'] . ' - ' . $shop['additional_id'] . " количество продуктов: " . count($market->catalogs). " количество обновлений: " . $updatedProductCount . "\n";
            }
        } catch (\Exception $e) {
            print_r($e);
        }

        if (!empty($this->errorMessages)) {
            Yii::$app->slack->send('Synchronize V2 product error', ':thumbs_up:', [
                [
                    'fallback' => 'Log message',
                    'color' => Yii::$app->slack->getLevelColor(Logger::LEVEL_ERROR),
                    'fields' => [
                        [
                            'title' => 'Application ID',
                            'value' => Yii::$app->id,
                            'short' => true,
                        ],
                        [
                            'title' => 'Error',
                            'value' => implode('; ', $this->errorMessages),
                            'short' => true,
                        ]
                    ],
                ],
            ]);
        }
    }
}