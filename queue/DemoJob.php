<?php
namespace app\queue;

use app\delivery\Deliveries;
use app\models\Address;
use app\models\forms\OrdersCourierCall;
use app\models\Order;
use app\models\OrderDelivery;
use app\models\Product;
use app\models\Shop;
use app\models\Warehouse;
use Yii;
use yii\base\BaseObject;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;

class DemoJob extends BaseObject implements RetryableJobInterface
{
    public $userId;

    public $demoLabels = [
        'a292d8c8e2ffea67115be64e04bc432b.pdf',
        'e25469bdf2d85a29fbb00d26542da215.pdf',
        'f7eab8de9c5436adc17f23b92d689b75.pdf',
    ];

    public $demoRegistries = [
        '44356a6415065345efdf4aac6e400cd7.pdf',
        '90704e4494bf4c19e3a1791d61cbb377.pdf',
        'dba52bfb724948efd2e4f6be6283fffb.pdf',
        'ecdb16a43c3c3ff25743320c00b015df.pdf',
    ];

    public $shopFill = [
        'name' => 'Тестовый магазин 1',
        'url'  => 'http://test.ru',
    ];

    public $productFillCount = 5;
    public $productFill = [
        'name'    => 'Тестовый товар ',
        'barcode' => 'SKU-',
        'price'   => 990,
        'weight'  => 500,
        'count'   => 10,
    ];

    public $orderFillCount = 10;
    public $orderFill = [
        'shop_order_number' => 'ID000-',
    ];

    public $users = [
        'Иванов Иван Иванович',
        'Сидоров Олег Евгеньевич',
        'Михалков Сергей Николаевич',
        'Ильин Василий Петрович',
        'Самарин Владимир Олегович',
        'Варна Ирина Андреевна',
        'Сысоева Екатерина Дмитриевна',
        'Бузина Светлана Александровна',
        'Михасов Евгений Николаевич',
        'Аверина Марина Артуровна',
    ];

    public $emails = [
        'test1@mail.ru',
        'test2@gmail.com',
        'test3@yahoo.com',
        'test4@bk.ru',
        'test5@list.ru',
        'test6@yandex.ru',
        'test7@mail.ru',
        'test8@gmail.com',
        'test9@rambler.ru',
        'test10@test.ru',
    ];

    public $warehouseFillCount = 2;

    public $phones = [
        '+7 (969) 769-22-22',
        '+7 (900) 123-54-21',
        '+7 (925) 571-90-60',
        '+7 (969) 769-32-45',
        '+7 (901) 123-54-21',
        '+7 (999) 577-92-92',
        '+7 (969) 769-22-22',
        '+7 (901) 125-45-12',
        '+7 (920) 574-92-62',
        '+7 (929) 999-12-53',
    ];
    public $warehouseFill = [
        [
            'name'          => 'Новый склад в Москве',
            'contact_fio'   => 'Иванов Иван Петрович',
            'contact_phone' => '+7 (999) 999-99-22',
        ],
        [
            'name'          => 'Тестовый склад в Самаре',
            'contact_fio'   => 'Арбузов Дмитрий Константинович',
            'contact_phone' => '+7 (962) 194-99-22',
        ],
    ];
    public $warehouseAddressFill = [
        [
            'country'        => 'RUS',
            'region'         => 'г Москва',
            'region_fias_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'city'           => 'г Москва',
            'city_fias_id'   => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'street'         => 'ул Лесная',
            'street_fias_id' => '8a63e928-8daf-4298-a30f-c19a770520ea',
            'house'          => '28',
            'flat'           => '11',
            'housing'        => '17',
            'postcode'       => '125047',
            'full_address'   => 'г Москва, ул Лесная, д 28 стр 17, оф 11',
            'address_object' => '{"value":"г Москва, ул Лесная, д 28 стр 17, оф 11","unrestricted_value":"г Москва, Тверской р-н, ул Лесная, д 28 стр 17, оф 11","data":{"postal_code":"125047","country":"Россия","region_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","region_kladr_id":"7700000000000","region_with_type":"г Москва","region_type":"г","region_type_full":"город","region":"Москва","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","city_kladr_id":"7700000000000","city_with_type":"г Москва","city_type":"г","city_type_full":"город","city":"Москва","city_area":"Центральный","city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"Тверской р-н","city_district_type":"р-н","city_district_type_full":"район","city_district":"Тверской","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"8a63e928-8daf-4298-a30f-c19a770520ea","street_kladr_id":"77000000000056100","street_with_type":"ул Лесная","street_type":"ул","street_type_full":"улица","street":"Лесная","house_fias_id":"49ca4c93-d8b3-4d5e-8741-8732e4a62ffe","house_kladr_id":"7700000000005610020","house_type":"д","house_type_full":"дом","house":"28","block_type":"стр","block_type_full":"строение","block":"17","flat_type":"оф","flat_type_full":"офис","flat":"11","flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"49ca4c93-d8b3-4d5e-8741-8732e4a62ffe","fias_level":"8","kladr_id":"7700000000005610020","capital_marker":"0","okato":"45286585000","oktmo":"45382000","tax_office":"7707","tax_office_legal":null,"timezone":null,"geo_lat":"55.7813704","geo_lon":"37.5959845","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"г Москва, Тверской р-н, ул Лесная, д 28 стр 17, оф 11","qc":null}}',
        ],
        [
            'country'        => 'RUS',
            'region'         => 'Самарская обл',
            'region_fias_id' => 'df3d7359-afa9-4aaa-8ff9-197e73906b1c',
            'city'           => 'г Самара',
            'city_fias_id'   => 'bb035cc3-1dc2-4627-9d25-a1bf2d4b936b',
            'street'         => 'пр-кт Ленина',
            'street_fias_id' => '7b7eee0a-d41a-4af0-8764-c89d582cc1b4',
            'house'          => '1',
            'flat'           => '53',
            'postcode'       => '443096',
            'full_address'   => 'г Самара, пр-кт Ленина, д 1, кв 53',
            'address_object' => '{"value":"г Самара, пр-кт Ленина, д 1, кв 53","unrestricted_value":"Самарская обл, г Самара, Октябрьский р-н, пр-кт Ленина, д 1, кв 53","data":{"postal_code":"443096","country":"Россия","region_fias_id":"df3d7359-afa9-4aaa-8ff9-197e73906b1c","region_kladr_id":"6300000000000","region_with_type":"Самарская обл","region_type":"обл","region_type_full":"область","region":"Самарская","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"bb035cc3-1dc2-4627-9d25-a1bf2d4b936b","city_kladr_id":"6300000100000","city_with_type":"г Самара","city_type":"г","city_type_full":"город","city":"Самара","city_area":null,"city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"Октябрьский р-н","city_district_type":"р-н","city_district_type_full":"район","city_district":"Октябрьский","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"7b7eee0a-d41a-4af0-8764-c89d582cc1b4","street_kladr_id":"63000001000076200","street_with_type":"пр-кт Ленина","street_type":"пр-кт","street_type_full":"проспект","street":"Ленина","house_fias_id":"8afd43eb-1496-4b1a-9c99-6975a6c1d45b","house_kladr_id":"6300000100007620005","house_type":"д","house_type_full":"дом","house":"1","block_type":null,"block_type_full":null,"block":null,"flat_type":"кв","flat_type_full":"квартира","flat":"53","flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"8afd43eb-1496-4b1a-9c99-6975a6c1d45b","fias_level":"8","kladr_id":"6300000100007620005","capital_marker":"2","okato":"36401385000","oktmo":"36701330","tax_office":"6316","tax_office_legal":null,"timezone":null,"geo_lat":"53.2049856","geo_lon":"50.1334331","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"Самарская обл, г Самара, Октябрьский р-н, пр-кт Ленина, д 1, кв 53","qc":null}}',
        ],
    ];

    public $orderAddressFill = [
        [
            'country'        => 'RUS',
            'region'         => 'г Москва',
            'region_fias_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'city'           => 'г Москва',
            'city_fias_id'   => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'street'         => 'ул Лесная',
            'street_fias_id' => '8a63e928-8daf-4298-a30f-c19a770520ea',
            'house'          => '28',
            'flat'           => '11',
            'housing'        => '17',
            'postcode'       => '125047',
            'full_address'   => 'г Москва, ул Лесная, д 28 стр 17, оф 11',
            'address_object' => '{"value":"г Москва, ул Лесная, д 28 стр 17, оф 11","unrestricted_value":"г Москва, Тверской р-н, ул Лесная, д 28 стр 17, оф 11","data":{"postal_code":"125047","country":"Россия","region_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","region_kladr_id":"7700000000000","region_with_type":"г Москва","region_type":"г","region_type_full":"город","region":"Москва","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","city_kladr_id":"7700000000000","city_with_type":"г Москва","city_type":"г","city_type_full":"город","city":"Москва","city_area":"Центральный","city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"Тверской р-н","city_district_type":"р-н","city_district_type_full":"район","city_district":"Тверской","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"8a63e928-8daf-4298-a30f-c19a770520ea","street_kladr_id":"77000000000056100","street_with_type":"ул Лесная","street_type":"ул","street_type_full":"улица","street":"Лесная","house_fias_id":"49ca4c93-d8b3-4d5e-8741-8732e4a62ffe","house_kladr_id":"7700000000005610020","house_type":"д","house_type_full":"дом","house":"28","block_type":"стр","block_type_full":"строение","block":"17","flat_type":"оф","flat_type_full":"офис","flat":"11","flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"49ca4c93-d8b3-4d5e-8741-8732e4a62ffe","fias_level":"8","kladr_id":"7700000000005610020","capital_marker":"0","okato":"45286585000","oktmo":"45382000","tax_office":"7707","tax_office_legal":null,"timezone":null,"geo_lat":"55.7813704","geo_lon":"37.5959845","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"г Москва, Тверской р-н, ул Лесная, д 28 стр 17, оф 11","qc":null}}',
        ],
        [
            'country'        => 'RUS',
            'region'         => 'г Москва',
            'region_fias_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'city'           => 'г Москва',
            'city_fias_id'   => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'street'         => 'ул Профсоюзная',
            'street_fias_id' => '9a661426-e65c-4223-af4d-aa5e95b7caac',
            'house'          => '123',
            'flat'           => '15',
            'housing'        => '1',
            'postcode'       => '117437',
            'full_address'   => 'г Москва, ул Профсоюзная, д 123 к 1, кв 15',
            'address_object' => '{"value":"г Москва, ул Профсоюзная, д 123 к 1, кв 15","unrestricted_value":"г Москва, р-н Коньково, ул Профсоюзная, д 123 к 1, кв 15","data":{"postal_code":"117437","country":"Россия","region_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","region_kladr_id":"7700000000000","region_with_type":"г Москва","region_type":"г","region_type_full":"город","region":"Москва","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","city_kladr_id":"7700000000000","city_with_type":"г Москва","city_type":"г","city_type_full":"город","city":"Москва","city_area":"Юго-западный","city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"р-н Коньково","city_district_type":"р-н","city_district_type_full":"район","city_district":"Коньково","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"9a661426-e65c-4223-af4d-aa5e95b7caac","street_kladr_id":"77000000000239200","street_with_type":"ул Профсоюзная","street_type":"ул","street_type_full":"улица","street":"Профсоюзная","house_fias_id":"bc390a07-3d41-468d-a78b-0485fa195ce8","house_kladr_id":"7700000000023920491","house_type":"д","house_type_full":"дом","house":"123","block_type":"к","block_type_full":"корпус","block":"1","flat_type":"кв","flat_type_full":"квартира","flat":"15","flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"bc390a07-3d41-468d-a78b-0485fa195ce8","fias_level":"8","kladr_id":"7700000000023920491","capital_marker":"0","okato":"45293566000","oktmo":"45902000","tax_office":"7728","tax_office_legal":null,"timezone":null,"geo_lat":"55.6362232","geo_lon":"37.5200191","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"г Москва, Москва, ул. Профсоюзная, 118к1, 137","qc":null}}'
        ],
        [
            'country'        => 'RUS',
            'region'         => 'г Санкт-Петербург',
            'region_fias_id' => 'c2deb16a-0330-4f05-821f-1d09c93331e6',
            'city'           => 'г Санкт-Петербург',
            'city_fias_id'   => 'c2deb16a-0330-4f05-821f-1d09c93331e6',
            'street'         => 'Торфяная дорога',
            'street_fias_id' => '9e8bc9e3-e687-4c1e-a9cb-283209129e21',
            'house'          => '2',
            'flat'           => '',
            'housing'        => '1',
            'postcode'       => '197374',
            'full_address'   => 'г Санкт-Петербург, Торфяная дорога, д 5 к 2',
            'address_object' => '{"value":"г Санкт-Петербург, Торфяная дорога, д 5 к 2","unrestricted_value":"г Санкт-Петербург, Приморский р-н, Торфяная дорога, д 5 к 2","data":{"postal_code":"197374","country":"Россия","region_fias_id":"c2deb16a-0330-4f05-821f-1d09c93331e6","region_kladr_id":"7800000000000","region_with_type":"г Санкт-Петербург","region_type":"г","region_type_full":"город","region":"Санкт-Петербург","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"c2deb16a-0330-4f05-821f-1d09c93331e6","city_kladr_id":"7800000000000","city_with_type":"г Санкт-Петербург","city_type":"г","city_type_full":"город","city":"Санкт-Петербург","city_area":null,"city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"Приморский р-н","city_district_type":"р-н","city_district_type_full":"район","city_district":"Приморский","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"9e8bc9e3-e687-4c1e-a9cb-283209129e21","street_kladr_id":"78000000000140300","street_with_type":"Торфяная дорога","street_type":"дор","street_type_full":"дорога","street":"Торфяная","house_fias_id":"93365da1-10ae-41b7-88ee-10456a7261db","house_kladr_id":"7800000000014030029","house_type":"д","house_type_full":"дом","house":"2","block_type":"к","block_type_full":"корпус","block":"1","flat_type":null,"flat_type_full":null,"flat":null,"flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"93365da1-10ae-41b7-88ee-10456a7261db","fias_level":"8","kladr_id":"7800000000014030029","capital_marker":"0","okato":"40270562000","oktmo":"40322000","tax_office":"7814","tax_office_legal":null,"timezone":null,"geo_lat":"59.9886625","geo_lon":"30.254973","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"г Санкт-Петербург, Торфяная дорога, д 2 к 1","qc":null}}'
        ],
        [
            'country'        => 'RUS',
            'region'         => 'Самарская обл',
            'region_fias_id' => 'df3d7359-afa9-4aaa-8ff9-197e73906b1c',
            'city'           => 'г Самара',
            'city_fias_id'   => 'bb035cc3-1dc2-4627-9d25-a1bf2d4b936b',
            'street'         => 'пр-кт Ленина',
            'street_fias_id' => '7b7eee0a-d41a-4af0-8764-c89d582cc1b4',
            'house'          => '1',
            'flat'           => '53',
            'postcode'       => '443096',
            'full_address'   => 'г Самара, пр-кт Ленина, д 1, кв 53',
            'address_object' => '{"value":"г Самара, пр-кт Ленина, д 1, кв 53","unrestricted_value":"Самарская обл, г Самара, Октябрьский р-н, пр-кт Ленина, д 1, кв 53","data":{"postal_code":"443096","country":"Россия","region_fias_id":"df3d7359-afa9-4aaa-8ff9-197e73906b1c","region_kladr_id":"6300000000000","region_with_type":"Самарская обл","region_type":"обл","region_type_full":"область","region":"Самарская","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"bb035cc3-1dc2-4627-9d25-a1bf2d4b936b","city_kladr_id":"6300000100000","city_with_type":"г Самара","city_type":"г","city_type_full":"город","city":"Самара","city_area":null,"city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"Октябрьский р-н","city_district_type":"р-н","city_district_type_full":"район","city_district":"Октябрьский","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"7b7eee0a-d41a-4af0-8764-c89d582cc1b4","street_kladr_id":"63000001000076200","street_with_type":"пр-кт Ленина","street_type":"пр-кт","street_type_full":"проспект","street":"Ленина","house_fias_id":"8afd43eb-1496-4b1a-9c99-6975a6c1d45b","house_kladr_id":"6300000100007620005","house_type":"д","house_type_full":"дом","house":"1","block_type":null,"block_type_full":null,"block":null,"flat_type":"кв","flat_type_full":"квартира","flat":"53","flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"8afd43eb-1496-4b1a-9c99-6975a6c1d45b","fias_level":"8","kladr_id":"6300000100007620005","capital_marker":"2","okato":"36401385000","oktmo":"36701330","tax_office":"6316","tax_office_legal":null,"timezone":null,"geo_lat":"53.2049856","geo_lon":"50.1334331","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"Самарская обл, г Самара, Октябрьский р-н, пр-кт Ленина, д 1, кв 53","qc":null}}',
        ],
    ];

    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws \Throwable
     */
    public function execute($queue)
    {
        $orderIds    = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {

            /**
             * Создаем 2 адреса склада
             */
            $warehousesAddress = [];
            for ($i = 1; $i <= $this->warehouseFillCount; $i++) {
                $address = new Address();
                $address->load($this->warehouseAddressFill[$i - 1], '');
                $address->save(false);
                $warehousesAddress[] = $address;
            }

            /**
             * Создаем 2 склада
             */
            $warehouses = [];
            for ($i = 1; $i <= $this->warehouseFillCount; $i++) {
                $warehouse = new Warehouse();
                $warehouse->load($this->warehouseFill[$i - 1], '');
                $warehouse->address_id = $warehousesAddress[$i - 1]->id;
                $warehouse->save(false);
                (new Query())->createCommand()->insert('{{%user_warehouse}}', ['user_id' => $this->userId, 'warehouse_id' => $warehouse->id])->execute();
                $warehouses[] = $warehouse;
            }

            /**
             * Создаем магазин
             */
            $shop = new Shop();
            $shop->load($this->shopFill, '');
            $shop->default_warehouse_id = $this->getWarehouse($warehouses)->id;
            $shop->save(false);

            (new Query())->createCommand()->insert('{{%user_shop}}', ['user_id' => $this->userId, 'shop_id' => $shop->id])->execute();
            (new Query())->createCommand()->insert('{{%shop_delivery}}', ['shop_id' => $shop->id, 'delivery_id' => 2])->execute();
            (new Query())->createCommand()->insert('{{%shop_delivery}}', ['shop_id' => $shop->id, 'delivery_id' => 3])->execute();
            (new Query())->createCommand()->insert('{{%shop_delivery}}', ['shop_id' => $shop->id, 'delivery_id' => 4])->execute();

            /**
             * Создаем 5 товаров
             */
            $products = [];
            for ($i = 1; $i <= $this->productFillCount; $i++) {
                $product = new Product();
                $product->load($this->productFill, '');
                $product->name    .= $i;
                $product->barcode .= $i;
                $product->price   *= $i;
                $product->count   *= $i;
                $product->shop_id = $shop->id;
                $product->save(false);
                $products[] = $product;
            }

            /**
             * Создаем адреса заказов
             */
            $orderAddresses = [];
            for ($i = 1; $i <= $this->orderFillCount; $i++) {
                $address = new Address();
                $address->load($this->getOrderAddress($this->orderAddressFill), '');
                $address->save(false);
                $orderAddresses[] = $address;
            }

            /**
             * Создаем заказы
             */
            $orders = [];
            for ($i = 1; $i <= $this->orderFillCount; $i++) {
                $order = new Order();
                $order->load($this->orderFill, '');
                $order->shop_order_number .= $i;
                $order->fio               = $this->getRandomUserFio();
                $order->label_url         = $this->getRandomLabelUrl();
                $order->email             = $this->getRandomEmail();
                $order->phone             = $this->getRandomPhone();

                /** @var Address $address */
                $address = $orderAddresses[$i - 1];

                $order->address_id     = $address->id;
                $order->products       = $this->getProducts($products);
                $order->warehouse_id   = $this->getWarehouse($warehouses)->id;
                $order->payment_method = Order::PAYMENT_METHOD_FULL_PAY;

                /** @var Deliveries $deliveries */
                $deliveries      = Yii::createObject(Deliveries::className(), [
                    $order,
                    new OrderDelivery(),
                ]);
                $orderDeliveries = $deliveries->calculate();

                $orderDelivery              = $this->getOrderDelivery($orderDeliveries);
                $orderDelivery->pickup_type = OrderDelivery::PICKUP_TYPE_FROM_DOOR;
                $orderDelivery->point_id    = 267;

                $order->delivery = $orderDelivery;

                $order->shop_id = $shop->id;

                if ($i == 3 || $i == 7) {
                    $order->sendToStatus(Order::STATUS_CREATED);
                    $order->sendToStatus(Order::STATUS_IN_COLLECTING);
                    $order->sendToStatus(Order::STATUS_READY_FOR_DELIVERY);
                } elseif ($i == 2 || $i == 4) {
                    $order->sendToStatus(Order::STATUS_CREATED);
                    $order->sendToStatus(Order::STATUS_IN_COLLECTING);
                } elseif ($i == 1 || $i == 8) {
                    $order->sendToStatus(Order::STATUS_CREATED);
                    $order->sendToStatus(Order::STATUS_IN_COLLECTING);
                    $order->sendToStatus(Order::STATUS_READY_FOR_DELIVERY);
                    $order->sendToStatus(Order::STATUS_WAITING_COURIER);
                    $order->sendToStatus(Order::STATUS_IN_DELIVERY);
                } elseif ($i == 5 || $i == 9) {
                    $order->sendToStatus(Order::STATUS_CREATED);
                    $order->sendToStatus(Order::STATUS_IN_COLLECTING);
                    $order->sendToStatus(Order::STATUS_READY_FOR_DELIVERY);
                    $order->sendToStatus(Order::STATUS_WAITING_COURIER);
                    $order->sendToStatus(Order::STATUS_IN_DELIVERY);
                    $order->sendToStatus(Order::STATUS_DELIVERED);
                } else {
                    $order->sendToStatus(Order::STATUS_CREATED);
                }

                if ($order->save(true)) {
                    if ($order->status == $order->getWorkflowStatusId(Order::STATUS_READY_FOR_DELIVERY)) {
                        $orderIds[] = $order->id;
                    }
                    $orders[] = $order;
                }
            }

            /**
             * Создаем вызовы курьеров
             */
            if ($orderIds) {
                $ordersCourierCall           = new OrdersCourierCall();
                $ordersCourierCall->orderIds = ArrayHelper::map(
                    Order::find()->select('order.id as id')
                        ->where(['order.id' => $orderIds])
                        ->joinWith(['delivery'])
                        ->andWhere('order_delivery.carrier_key IS NOT NULL')
                        ->andWhere('order_delivery.carrier_key != "dpd" ')
                        ->all(), 'id', 'id'
                );
                if ($couriers = $ordersCourierCall->call()) {
                    foreach ($couriers as $courier) {
                        $courier->registry_label_url = $this->getRandomRegistriesUrl();
                        $courier->courier_call       = 1;
                        $courier->save();
                    }
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param $warehouses Warehouse[]
     * @return Warehouse
     */
    private function getWarehouse($warehouses)
    {
        $id = rand(0, count($warehouses) - 1);
        return $warehouses[$id];
    }

    /**
     * @param $addresses
     * @return array
     */
    private function getOrderAddress($addresses)
    {
        $id = rand(0, count($addresses) - 1);
        return $addresses[$id];
    }

    /**
     * @return string
     */
    private function getRandomLabelUrl()
    {
        $id = rand(0, count($this->demoLabels) - 1);
        return Url::to('/docs/demo/' . $this->demoLabels[$id], true);
    }

    /**
     * @return string
     */
    private function getRandomPhone()
    {
        $id = rand(0, count($this->phones) - 1);
        return $this->phones[$id];
    }

    /**
     * @return string
     */
    private function getRandomEmail()
    {
        $id = rand(0, count($this->emails) - 1);
        return $this->emails[$id];
    }

    /**
     * @return string
     */
    private function getRandomUserFio()
    {
        $id = rand(0, count($this->users) - 1);
        return $this->users[$id];
    }

    /**
     * @param $products Product[]
     * @return Product[]
     */
    private function getProducts($products)
    {
        $resultProducts = [];
        $count          = rand(1, count($products));

        for ($i = 1; $i <= $count; $i++) {
            $id = rand(0, count($products) - 1);
            $resultProducts[] = $products[$id];
            unset($products[$id]);
            $products = array_values($products);
        }

        return $resultProducts;
    }

    /**
     * @param $orderDeliveries OrderDelivery[]
     * @return OrderDelivery
     */
    private function getOrderDelivery($orderDeliveries)
    {
        $id = rand(0, count($orderDeliveries) - 1);
        return $orderDeliveries[$id];
    }

    /**
     * @return string
     */
    private function getRandomRegistriesUrl()
    {
        $id = rand(0, count($this->demoRegistries) - 1);
        return Url::to('/docs/demo/' . $this->demoRegistries[$id], true);
    }

    /**
     * @return int time to reserve in seconds
     */
    public function getTtr()
    {
        return 5 * 60;
    }

    /**
     * @param int                   $attempt number
     * @param \Exception|\Throwable $error   from last execute of the job
     * @return bool
     */
    public function canRetry($attempt, $error)
    {
        file_put_contents('job.log', $error->getMessage(), FILE_APPEND | LOCK_EX);
        return true;
    }
}