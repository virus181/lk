<?php

namespace app\api\view\Shop;

use app\models\Shop;
use Yii;

class Phones
{
    const QUERY_MANAGERS = 'managers';
    const QUERY_PHONES = 'phones';
    const QUERY_TARIFF = 'tariff';
    const QUERY_OPTION = 'option';

    /** @var Shop[] */
    private $shops;
    /** @var bool */
    private $includePhones;
    /** @var bool */
    private $includeTariff;
    /** @var bool */
    private $includeOption;
    /** @var bool */
    private $includeManagers;

    /**
     * @return array
     */
    public function build()
    {
        $result = [];

        foreach ($this->shops as $shop) {
            if ($shop->phones) {

                $data = [
                    'id'        => $shop->id,
                    'name'      => $shop->name,
                    'is_active' => $shop->status == Shop::STATUS_ACTIVE,
                ];

                if ($this->includePhones) {
                    $phones = [];
                    foreach ($shop->phones as $phone) {
                        $phones[] = [
                            'number'   => $phone->phone,
                            'provider' => Yii::t('shop', $phone->provider_code)
                        ];
                    }

                    $data['phones'] = $phones ?? [];
                }

                if ($this->includeOption && $shop->option) {
                    $data['options'] = [
                        'first_queue'     => Yii::t('manager', $shop->option->first_queue),
                        'second_queue'    => Yii::t('manager', $shop->option->second_queue),
                        'third_queue'     => Yii::t('manager', $shop->option->third_queue),
                        'work_scheme_url' => $shop->option->work_scheme_url
                    ];
                }

                if ($this->includeManagers && $shop->managers) {
                    $managers = [];
                    foreach ($shop->managers as $manager) {
                        $managers[] = [
                            'id'  => $manager->id,
                            'fio' => $manager->fio
                        ];
                    }
                    $data['managers'] = $managers ?? [];
                }

                if ($this->includeTariff && $shop->tariff) {
                    $data['tariff'] = [
                        'id'        => $shop->tariff->id,
                        'name'      => Yii::t('shop', $shop->tariff->code),
                        'work_time' => Yii::t('shop', sprintf('Work scheme for %s tariff', $shop->tariff->code)),
                    ];
                }

                $result['shops'][] = $data;
            }
        }

        return $result;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setIncludeParams(array $params)
    {
        $this->includeManagers = in_array(self::QUERY_MANAGERS, $params);
        $this->includeOption = in_array(self::QUERY_OPTION, $params);
        $this->includeTariff = in_array(self::QUERY_TARIFF, $params);
        $this->includePhones = in_array(self::QUERY_PHONES, $params);
        return $this;
    }

    /**
     * @param array $shops
     * @return $this
     */
    public function setShops(array $shops)
    {
        $this->shops = $shops;
        return $this;
    }
}