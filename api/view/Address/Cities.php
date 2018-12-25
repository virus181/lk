<?php
namespace app\api\view\Address;

use app\api\models\Suggestion;

class Cities
{
    /** @var array */
    private $suggestions;

    /**
     * @return array
     */
    public function build()
    {
        $result = [];
        if ($this->suggestions) {
            foreach ($this->suggestions as $suggestion) {
                $suggest = (new \app\api\builder\Suggestion($suggestion))->getSuggestion();
                $city = new Suggestion\City();
                $city->country = $suggest->data->country;
                $city->regionWithType = $suggest->data->regionWithType;
                $city->regionFiasId = $suggest->data->regionFiasId;
                $city->city = $suggest->data->city ? $suggest->data->city : $suggest->data->settlement;
                $city->cityFiasId = $suggest->data->cityFiasId ? $suggest->data->cityFiasId : $suggest->data->settlementFiasId;
                $city->cityType = $suggest->data->cityType ? $suggest->data->cityType : $suggest->data->settlementType;
                $city->cityTypeFull = $suggest->data->cityTypeFull ? $suggest->data->cityTypeFull : $suggest->data->settlementTypeFull;
                $city->cityWithType = $suggest->data->cityWithType ? $suggest->data->cityWithType : $suggest->data->settlementWithType;
                $result[] = $city;
            }
        }

        return $result;
    }

    /**
     * @param array $suggestions
     * @return $this
     */
    public function setSuggestions(array $suggestions)
    {
        $this->suggestions = $suggestions;
        return $this;
    }
}