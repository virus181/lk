<?php
namespace app\api\builder;

class Suggestion
{
    const EXCLUDED_SETTLEMENT_TYPES = [
        'мкр', 'тер', 'р-н', 'поселок', 'жилрайон'
    ];

    /** @var \app\api\models\Suggestion */
    private $suggestion;

    public function __construct(array $suggestion)
    {
        $data = new \app\api\models\Suggestion\Data();

        $data->postalCode = $suggestion['data']['postal_code'];
        $data->country = $suggestion['data']['country'];
        $data->region = $suggestion['data']['region'];
        $data->regionFiasId = $suggestion['data']['region_fias_id'];
        $data->regionType = $suggestion['data']['region_type'];
        $data->regionTypeFull = $suggestion['data']['region_type_full'];
        $data->regionWithType = $suggestion['data']['region_with_type'];

        // Заполним город
        $data->city = $suggestion['data']['city'];
        $data->cityType = $suggestion['data']['city_type'];
        $data->cityTypeFull = $suggestion['data']['city_type_full'];
        $data->cityWithType = $suggestion['data']['city_with_type'];
        $data->cityFiasId = $suggestion['data']['city_fias_id'];

        // Если пустой город, или не пустой и присутствует settlement и его тип не в исключаемых, то берем его
        if ((empty($suggestion['data']['city_with_type']))
            || (!empty($suggestion['data']['city_with_type'])
                && !empty($suggestion['data']['settlement_with_type'])
                && !in_array($suggestion['data']['settlement_type'], self::EXCLUDED_SETTLEMENT_TYPES)
            )
        ) {
            $data->city = $suggestion['data']['settlement'];
            $data->cityType = $suggestion['data']['settlement_type'];
            $data->cityTypeFull = $suggestion['data']['settlement_type_full'];
            $data->cityWithType = $suggestion['data']['settlement_with_type'];
            $data->cityFiasId = $suggestion['data']['settlement_fias_id'];
        }

        $data->street = $suggestion['data']['street'] ? $suggestion['data']['street'] : 'Без улицы';
        $data->streetType = $suggestion['data']['street_type'] ? $suggestion['data']['street_type'] : 'ул';
        $data->streetTypeFull = $suggestion['data']['street_type_full'] ? $suggestion['data']['street_type_full'] : 'улица';
        $data->streetWithType = $suggestion['data']['street_with_type'] ? $suggestion['data']['street_with_type'] : 'Без улицы';
        $data->streetFiasId = $suggestion['data']['street_fias_id'] ? $suggestion['data']['street_fias_id'] : '-';

        $data->house = $suggestion['data']['house'];
        $data->houseFiasId = $suggestion['data']['house_fias_id'];
        $data->houseType = $suggestion['data']['house_type'];
        $data->houseTypeFull = $suggestion['data']['house_type_full'];
        $data->block = $suggestion['data']['block'];
        $data->blockType = $suggestion['data']['block_type'];
        $data->blockTypeFull = $suggestion['data']['block_type_full'];
        $data->flat = $suggestion['data']['flat'];
        $data->flatType = $suggestion['data']['flat_type'];
        $data->flatTypeFull = $suggestion['data']['flat_type_full'];
        $data->geoLat = $suggestion['data']['geo_lat'];
        $data->geoLon = $suggestion['data']['geo_lon'];

        $this->suggestion = new \app\api\models\Suggestion(
            $suggestion['value'],
            $suggestion['unrestricted_value'],
            $data
        );
    }

    public function getSuggestion(): \app\api\models\Suggestion
    {
        return $this->suggestion;
    }
}