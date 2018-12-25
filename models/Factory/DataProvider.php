<?php
namespace app\models\Factory;

class DataProvider
{
    private $emails = [
        'ivanov@fastery.ru',
        'petrov91@fastery.ru',
        'vasechkin@lk.ru',
        'simonov98-80@mail.ru',
        'kirill@fastery.ru',
        'vitya-super@list.ru',
        'test@rambler.ru',
        'banana@gmail.ru',
        'kiwi@list.ru',
        'kiz@fastery.ru',
    ];

    private $phones = [
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

    private $siteNames = [
        'Рога и копыта',
        'Сайт номер 1 в России',
        'Тестовый сайт',
        'Сайт одежды',
        'Детские товары',
        'Магазин спортивных аксессуаров',
        'Для рыбалки',
    ];

    private $urls = [
        'https://yandex.ru',
        'https://rambler.ru',
        'https://google.com',
        'https://mail.ru',
        'https://vk.ru',
        'https://facebook.ru',
        'https://kg.com',
    ];

    private $names = [
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

    private $warehouseNames = [
        'Новый склад',
        'Склад',
        'Основной склад',
        'Тестовый склад',
        'Склад Фастери',
    ];

    private $productNames = [
        'Тестовый товар 1',
        'Тестовый товар 2',
        'Тестовый товар 3',
        'Тестовый товар 4',
        'Тестовый товар 5',
    ];

    private $statuses = [
        0,
        10,
    ];

    private $roundingOffPrefixes = [
        0,
        -1,
        1,
    ];

    private $roundingOffs = [
        0,
        10,
        100,
    ];

    public $addresses = [
        [
            'country' => 'RUS',
            'region' => 'г Москва',
            'region_fias_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'city' => 'г Москва',
            'city_fias_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'street' => 'ул Лесная',
            'street_fias_id' => '8a63e928-8daf-4298-a30f-c19a770520ea',
            'house' => '28',
            'flat' => '11',
            'housing' => '17',
            'postcode' => '125047',
            'full_address' => 'г Москва, ул Лесная, д 28 стр 17, оф 11',
            'address_object' => '{"value":"г Москва, ул Лесная, д 28 стр 17, оф 11","unrestricted_value":"г Москва, Тверской р-н, ул Лесная, д 28 стр 17, оф 11","data":{"postal_code":"125047","country":"Россия","region_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","region_kladr_id":"7700000000000","region_with_type":"г Москва","region_type":"г","region_type_full":"город","region":"Москва","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","city_kladr_id":"7700000000000","city_with_type":"г Москва","city_type":"г","city_type_full":"город","city":"Москва","city_area":"Центральный","city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"Тверской р-н","city_district_type":"р-н","city_district_type_full":"район","city_district":"Тверской","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"8a63e928-8daf-4298-a30f-c19a770520ea","street_kladr_id":"77000000000056100","street_with_type":"ул Лесная","street_type":"ул","street_type_full":"улица","street":"Лесная","house_fias_id":"49ca4c93-d8b3-4d5e-8741-8732e4a62ffe","house_kladr_id":"7700000000005610020","house_type":"д","house_type_full":"дом","house":"28","block_type":"стр","block_type_full":"строение","block":"17","flat_type":"оф","flat_type_full":"офис","flat":"11","flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"49ca4c93-d8b3-4d5e-8741-8732e4a62ffe","fias_level":"8","kladr_id":"7700000000005610020","capital_marker":"0","okato":"45286585000","oktmo":"45382000","tax_office":"7707","tax_office_legal":null,"timezone":null,"geo_lat":"55.7813704","geo_lon":"37.5959845","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"г Москва, Тверской р-н, ул Лесная, д 28 стр 17, оф 11","qc":null}}',
        ],
        [
            'country' => 'RUS',
            'region' => 'г Москва',
            'region_fias_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'city' => 'г Москва',
            'city_fias_id' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'street' => 'ул Профсоюзная',
            'street_fias_id' => '9a661426-e65c-4223-af4d-aa5e95b7caac',
            'house' => '123',
            'flat' => '15',
            'housing' => '1',
            'postcode' => '117437',
            'full_address' => 'г Москва, ул Профсоюзная, д 123 к 1, кв 15',
            'address_object' => '{"value":"г Москва, ул Профсоюзная, д 123 к 1, кв 15","unrestricted_value":"г Москва, р-н Коньково, ул Профсоюзная, д 123 к 1, кв 15","data":{"postal_code":"117437","country":"Россия","region_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","region_kladr_id":"7700000000000","region_with_type":"г Москва","region_type":"г","region_type_full":"город","region":"Москва","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5","city_kladr_id":"7700000000000","city_with_type":"г Москва","city_type":"г","city_type_full":"город","city":"Москва","city_area":"Юго-западный","city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"р-н Коньково","city_district_type":"р-н","city_district_type_full":"район","city_district":"Коньково","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"9a661426-e65c-4223-af4d-aa5e95b7caac","street_kladr_id":"77000000000239200","street_with_type":"ул Профсоюзная","street_type":"ул","street_type_full":"улица","street":"Профсоюзная","house_fias_id":"bc390a07-3d41-468d-a78b-0485fa195ce8","house_kladr_id":"7700000000023920491","house_type":"д","house_type_full":"дом","house":"123","block_type":"к","block_type_full":"корпус","block":"1","flat_type":"кв","flat_type_full":"квартира","flat":"15","flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"bc390a07-3d41-468d-a78b-0485fa195ce8","fias_level":"8","kladr_id":"7700000000023920491","capital_marker":"0","okato":"45293566000","oktmo":"45902000","tax_office":"7728","tax_office_legal":null,"timezone":null,"geo_lat":"55.6362232","geo_lon":"37.5200191","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"г Москва, Москва, ул. Профсоюзная, 118к1, 137","qc":null}}'
        ],
        [
            'country' => 'RUS',
            'region' => 'г Санкт-Петербург',
            'region_fias_id' => 'c2deb16a-0330-4f05-821f-1d09c93331e6',
            'city' => 'г Санкт-Петербург',
            'city_fias_id' => 'c2deb16a-0330-4f05-821f-1d09c93331e6',
            'street' => 'Торфяная дорога',
            'street_fias_id' => '9e8bc9e3-e687-4c1e-a9cb-283209129e21',
            'house' => '2',
            'flat' => '',
            'housing' => '1',
            'postcode' => '197374',
            'full_address' => 'г Санкт-Петербург, Торфяная дорога, д 5 к 2',
            'address_object' => '{"value":"г Санкт-Петербург, Торфяная дорога, д 5 к 2","unrestricted_value":"г Санкт-Петербург, Приморский р-н, Торфяная дорога, д 5 к 2","data":{"postal_code":"197374","country":"Россия","region_fias_id":"c2deb16a-0330-4f05-821f-1d09c93331e6","region_kladr_id":"7800000000000","region_with_type":"г Санкт-Петербург","region_type":"г","region_type_full":"город","region":"Санкт-Петербург","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"c2deb16a-0330-4f05-821f-1d09c93331e6","city_kladr_id":"7800000000000","city_with_type":"г Санкт-Петербург","city_type":"г","city_type_full":"город","city":"Санкт-Петербург","city_area":null,"city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"Приморский р-н","city_district_type":"р-н","city_district_type_full":"район","city_district":"Приморский","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"9e8bc9e3-e687-4c1e-a9cb-283209129e21","street_kladr_id":"78000000000140300","street_with_type":"Торфяная дорога","street_type":"дор","street_type_full":"дорога","street":"Торфяная","house_fias_id":"93365da1-10ae-41b7-88ee-10456a7261db","house_kladr_id":"7800000000014030029","house_type":"д","house_type_full":"дом","house":"2","block_type":"к","block_type_full":"корпус","block":"1","flat_type":null,"flat_type_full":null,"flat":null,"flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"93365da1-10ae-41b7-88ee-10456a7261db","fias_level":"8","kladr_id":"7800000000014030029","capital_marker":"0","okato":"40270562000","oktmo":"40322000","tax_office":"7814","tax_office_legal":null,"timezone":null,"geo_lat":"59.9886625","geo_lon":"30.254973","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"г Санкт-Петербург, Торфяная дорога, д 2 к 1","qc":null}}'
        ],
        [
            'country' => 'RUS',
            'region' => 'Самарская обл',
            'region_fias_id' => 'df3d7359-afa9-4aaa-8ff9-197e73906b1c',
            'city' => 'г Самара',
            'city_fias_id' => 'bb035cc3-1dc2-4627-9d25-a1bf2d4b936b',
            'street' => 'пр-кт Ленина',
            'street_fias_id' => '7b7eee0a-d41a-4af0-8764-c89d582cc1b4',
            'house' => '1',
            'flat' => '53',
            'postcode' => '443096',
            'full_address' => 'г Самара, пр-кт Ленина, д 1, кв 53',
            'address_object' => '{"value":"г Самара, пр-кт Ленина, д 1, кв 53","unrestricted_value":"Самарская обл, г Самара, Октябрьский р-н, пр-кт Ленина, д 1, кв 53","data":{"postal_code":"443096","country":"Россия","region_fias_id":"df3d7359-afa9-4aaa-8ff9-197e73906b1c","region_kladr_id":"6300000000000","region_with_type":"Самарская обл","region_type":"обл","region_type_full":"область","region":"Самарская","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"bb035cc3-1dc2-4627-9d25-a1bf2d4b936b","city_kladr_id":"6300000100000","city_with_type":"г Самара","city_type":"г","city_type_full":"город","city":"Самара","city_area":null,"city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":"Октябрьский р-н","city_district_type":"р-н","city_district_type_full":"район","city_district":"Октябрьский","settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"7b7eee0a-d41a-4af0-8764-c89d582cc1b4","street_kladr_id":"63000001000076200","street_with_type":"пр-кт Ленина","street_type":"пр-кт","street_type_full":"проспект","street":"Ленина","house_fias_id":"8afd43eb-1496-4b1a-9c99-6975a6c1d45b","house_kladr_id":"6300000100007620005","house_type":"д","house_type_full":"дом","house":"1","block_type":null,"block_type_full":null,"block":null,"flat_type":"кв","flat_type_full":"квартира","flat":"53","flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"8afd43eb-1496-4b1a-9c99-6975a6c1d45b","fias_level":"8","kladr_id":"6300000100007620005","capital_marker":"2","okato":"36401385000","oktmo":"36701330","tax_office":"6316","tax_office_legal":null,"timezone":null,"geo_lat":"53.2049856","geo_lon":"50.1334331","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"Самарская обл, г Самара, Октябрьский р-н, пр-кт Ленина, д 1, кв 53","qc":null}}',
        ],
        [
            'country' => 'RUS',
            'region' => 'Калужская обл',
            'region_fias_id' => '18133adf-90c2-438e-88c4-62c41656de70',
            'city' => 'г Калуга',
            'city_fias_id' => 'b502ae45-897e-4b6f-9776-6ff49740b537',
            'street' => 'ул Московская',
            'street_fias_id' => '20e7d4db-da49-47f4-a8b5-97d72e25fe5b',
            'house' => '193',
            'housing' => '2',
            'postcode' => '248021',
            'full_address' => 'г Калуга, ул Московская, д 193 к 2',
            'address_object' => '{"value":"г Калуга, ул Московская, д 193 к 2","unrestricted_value":"Калужская обл, г Калуга, ул Московская, д 193 к 2","data":{"postal_code":"248021","country":"Россия","region_fias_id":"18133adf-90c2-438e-88c4-62c41656de70","region_kladr_id":"4000000000000","region_with_type":"Калужская обл","region_type":"обл","region_type_full":"область","region":"Калужская","area_fias_id":null,"area_kladr_id":null,"area_with_type":null,"area_type":null,"area_type_full":null,"area":null,"city_fias_id":"b502ae45-897e-4b6f-9776-6ff49740b537","city_kladr_id":"4000000100000","city_with_type":"г Калуга","city_type":"г","city_type_full":"город","city":"Калуга","city_area":null,"city_district_fias_id":null,"city_district_kladr_id":null,"city_district_with_type":null,"city_district_type":null,"city_district_type_full":null,"city_district":null,"settlement_fias_id":null,"settlement_kladr_id":null,"settlement_with_type":null,"settlement_type":null,"settlement_type_full":null,"settlement":null,"street_fias_id":"20e7d4db-da49-47f4-a8b5-97d72e25fe5b","street_kladr_id":"40000001000052200","street_with_type":"ул Московская","street_type":"ул","street_type_full":"улица","street":"Московская","house_fias_id":"3f2131e2-f761-4b63-8f07-0d9f2068b1ee","house_kladr_id":"4000000100005220506","house_type":"д","house_type_full":"дом","house":"193","block_type":"к","block_type_full":"корпус","block":"2","flat_type":null,"flat_type_full":null,"flat":null,"flat_area":null,"square_meter_price":null,"flat_price":null,"postal_box":null,"fias_id":"3f2131e2-f761-4b63-8f07-0d9f2068b1ee","fias_level":"8","kladr_id":"4000000100005220506","capital_marker":"2","okato":"29401000000","oktmo":"29701000","tax_office":"4028","tax_office_legal":null,"timezone":null,"geo_lat":"54.5279804","geo_lon":"36.2698918","beltway_hit":null,"beltway_distance":null,"qc_geo":"0","qc_complete":null,"qc_house":null,"history_values":null,"unparsed_parts":null,"source":"Калужская обл, г Калуга, ул Московская, д 193 к 2","qc":null}}',
        ],
    ];

    private $orderDeliveries = [
        [
            'city' => 'г Москва',
            'type' => 'courier',
            'pickup_type' => 'on_terminal',
            'pickup_types' => '"[\"on_terminal\"]"',
            'carrier_key' => 'b2cpl',
            'tariff_id' => '155',
            'name' => 'Курьерская доставка',
            'class_name_provider' => 'app\delivery\apiship\Delivery',
            'time_start' => '10:00:00',
            'time_end' => '19:00:00',
        ],
        [
            'city' => 'г Москва',
            'type' => 'courier',
            'pickup_type' => 'on_terminal',
            'pickup_types' => '"[\"on_terminal\"]"',
            'carrier_key' => 'own',
            'tariff_id' => '33',
            'name' => 'Курьер ИМ',
            'class_name_provider' => 'app\delivery\own\Delivery',
            'time_start' => '10:00:00',
            'time_end' => '19:00:00',
        ],
        [
            'city' => 'г Москва',
            'type' => 'mail',
            'pickup_type' => 'on_terminal',
            'pickup_types' => '"[\"on_terminal\"]"',
            'carrier_key' => 'b2cpl',
            'tariff_id' => '166',
            'name' => 'Почта России - посылка 1-класса с объяв. ценностью и налож. платежом',
            'class_name_provider' => 'app\delivery\apiship\Delivery',
            'time_start' => '10:00:00',
            'time_end' => '19:00:00',
        ],
        [
            'city' => 'г Москва',
            'type' => 'point',
            'point_id' => '42637',
            'point_type' => 'pvz',
            'pickup_type' => 'from_door',
            'pickup_types' => '["from_door","on_terminal"]',
            'carrier_key' => 'boxberry',
            'tariff_id' => '44',
            'name' => 'Стандартный тариф',
            'class_name_provider' => 'app\delivery\apiship\Delivery',
            'time_start' => '10:00:00',
            'time_end' => '19:00:00',
            'phone' => '8-800-222-80-00',
        ],
        [
            'city' => 'г Москва',
            'type' => 'point',
            'point_id' => '11403',
            'point_type' => 'pvz',
            'pickup_type' => 'from_door',
            'pickup_types' => '"[\"from_door\",\"on_terminal\"]"',
            'carrier_key' => 'iml',
            'tariff_id' => '6',
            'name' => 'Стандартный тариф',
            'class_name_provider' => 'app\delivery\apiship\Delivery',
            'time_start' => '10:00:00',
            'time_end' => '19:00:00',
            'phone' => '8-495-988-49-05',
        ],
        [
            'city' => 'г Москва',
            'type' => 'courier',
            'pickup_type' => 'on_terminal',
            'pickup_types' => '"[\"on_terminal\"]"',
            'carrier_key' => 'cdek',
            'tariff_id' => '54',
            'name' => 'Посылка склад-дверь',
            'class_name_provider' => 'app\delivery\apiship\Delivery',
            'time_start' => '10:00:00',
            'time_end' => '19:00:00',
        ],
    ];

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->emails[array_rand($this->emails, 1)];
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phones[array_rand($this->phones, 1)];
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->statuses[array_rand($this->statuses, 1)];
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    public function getRandomNumber(int $min = 1, int $max = 99): int
    {
        return rand($min, $max);
    }

    /**
     * @return string
     */
    public function getBarcode(): string
    {
        $code = (md5(time() + rand(0, 9)));
        return 'sku-' . substr($code, 24);
    }

    /**
     * @return string
     */
    public function getOrderStatus(): string
    {
        // Пока только один статус, потом добавим рандомные статусы
        return 'OrderWorkflow/' . \app\models\Order::STATUS_CREATED;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        // Пока только один метод
        return \app\models\Order::PAYMENT_METHOD_FULL_PAY;
    }

    /**
     * @return int
     */
    public function getRandomBoolean(): int
    {
        return rand(0, 1);
    }

    /**
     * @return string
     */
    public function getFio(): string
    {
        return $this->names[array_rand($this->names, 1)];
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productNames[array_rand($this->productNames, 1)];
    }

    /**
     * @return array
     */
    public function getOrderDelivery(): array
    {
        return $this->orderDeliveries[array_rand($this->orderDeliveries, 1)];
    }

    /**
     * @param int $days
     * @return int
     */
    public function getTime(int $days = 0): int
    {
        $time = strtotime(date('Y-m-d', time()));
        return $time + $days * 86400;
    }

    /**
     * @return string
     */
    public function getWarehouseName(): string
    {
        return $this->warehouseNames[array_rand($this->warehouseNames, 1)];
    }

    /**
     * @return string
     */
    public function getSiteName(): string
    {
        return $this->siteNames[array_rand($this->siteNames, 1)];
    }

    /**
     * @return int
     */
    public function getRoundOff(): int
    {
        return $this->roundingOffs[array_rand($this->roundingOffs, 1)];
    }

    /**
     * @return int
     */
    public function getRoundOffPrefix(): int
    {
        return $this->roundingOffPrefixes[array_rand($this->roundingOffPrefixes, 1)];
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->urls[array_rand($this->urls, 1)];
    }

    /**
     * @return string
     */
    public function getShopOrderNumber(): string
    {
        return 'test-' . substr(md5(time()), 26, 5);
    }

    /**
     * @param string $fiasId
     * @return array|null
     */
    public function getAddress(string $fiasId = ''): ?array
    {
        if ($fiasId) {
            foreach ($this->addresses as $address) {
                if ($address['city_fias_id'] == $fiasId) {
                    return $address;
                }
            }
            return null;
        } else {
            return $this->addresses[array_rand($this->addresses, 1)];
        }
    }
}