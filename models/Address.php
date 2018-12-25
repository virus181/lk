<?php

namespace app\models;

use app\api\view\Address\Suggestions;
use app\behaviors\LogBehavior;
use app\components\Clients\Dadata;
use app\models\queries\AddressQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%address}}".
 *
 * @property int $id
 * @property string $country
 * @property string $region
 * @property string $region_fias_id
 * @property string $city
 * @property string $city_fias_id
 * @property string $street
 * @property string $street_fias_id
 * @property string $house
 * @property string $flat
 * @property string $housing
 * @property string $building DEPRECATED
 * @property string $postcode
 * @property float $lat
 * @property float $lng
 * @property string $full_address
 * @property string $address_object
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Warehouse[] $warehouses
 */
class Address extends ActiveRecord
{
    const SCENARIO_ADDRESS_FULL = 'address_full';
    const SCENARIO_ADDRESS_API_FULL = 'address_api_full';
    const SCENARIO_ADDRESS_TO_CITY = 'address_to_city';
    const SCENARIO_ADDRESS_API_TO_CITY = 'address_api_to_city';

    const FORBIDDEN_SYMBOLS = [
        "-", " ", "_"
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%address}}';
    }

    /**
     * @inheritdoc
     * @return AddressQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AddressQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            LogBehavior::className(),
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['!country'], 'default', 'value' => 'RUS'],
            [['city', 'street', 'house'], 'validateAddress', 'skipOnError' => false, 'skipOnEmpty' => false, 'on' => self::SCENARIO_ADDRESS_FULL],
            [['city'], 'validateAddress', 'skipOnError' => false, 'skipOnEmpty' => false, 'on' => self::SCENARIO_ADDRESS_TO_CITY],
            [['city', 'street', 'house', 'city_fias_id', 'street_fias_id'], 'validateAddressApi', 'skipOnError' => false, 'skipOnEmpty' => false, 'on' => self::SCENARIO_ADDRESS_API_FULL],
            [['city', 'city_fias_id'], 'validateAddressApi', 'skipOnError' => false, 'skipOnEmpty' => false, 'on' => self::SCENARIO_ADDRESS_API_TO_CITY],
            [['address_object'], 'string'],
            [['region', 'region_fias_id', 'city', 'city_fias_id', 'street', 'street_fias_id'], 'string', 'max' => 255],
            [['house', 'flat', 'housing', 'building', 'postcode'], 'string', 'max' => 16],
            [['full_address'], 'string'],
            [['lat', 'lng'], 'number'],
            [['full_address'], 'filter', 'filter' => function ($value) {
                if ($value == null) {
                    if ($this->region) {
                        $value .= $this->region;
                    }
                    if ($this->city) {
                        if ($this->region) {
                            $value .= ', ';
                        }
                        $value .= $this->city;
                    }
                    if ($this->street) {
                        $value .= ', ' . $this->street;
                    }
                    if ($this->house) {
                        $value .= ', ' . $this->house;
                    }
                    if ($this->housing) {
                        $value .= ', ' . $this->housing;
                    }
                    if ($this->flat) {
                        $value .= ', ' . $this->flat;
                    }
                }

                return $value;
            }],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_ADDRESS_FULL] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_ADDRESS_API_FULL] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_ADDRESS_TO_CITY] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_ADDRESS_API_TO_CITY] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    public function validateAddress($attribute)
    {
        if (!$this->{$attribute}) {
            $this->addCustomError('full_address', $attribute);
        }
    }

    private function addCustomError($attribute, $attributeLabel = null)
    {
        $this->addError(
            $attribute,
            Yii::t('app', '{attribute} cannot be blank.', [
                'attribute' => Yii::t('app', $this->getAttributeLabel(($attributeLabel ? $attributeLabel : $attribute)))
            ])
        );
    }

    public function validateAddressApi($attribute)
    {
        $attributesFias = [
            'city' => 'city_fias_id',
            'street' => 'street_fias_id',
        ];

        if ($fiasAttribute = ArrayHelper::getValue($attributesFias, $attribute)) {
            if ($this->{$attribute} == false && $this->{$fiasAttribute} == false) {
                $this->addCustomError($attribute);
            }
        } elseif (!in_array($attribute, $attributesFias)) {
            if (!$this->{$attribute}) {
                $this->addCustomError($attribute);
            }
        }
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['created_at'] = function () {
            return date('Y-m-d H:i:s', $this->created_at);
        };
        $fields['updated_at'] = function () {
            return date('Y-m-d H:i:s', $this->updated_at);
        };

        if (Yii::$app->params['environment'] == 'api') {
            unset($fields['id']);
            unset($fields['created_at']);
            unset($fields['updated_at']);
        }

        unset($fields['address_object']);
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'country' => Yii::t('app', 'Country'),
            'region' => Yii::t('app', 'Region'),
            'region_fias_id' => Yii::t('app', 'Region Fias ID'),
            'city' => Yii::t('app', 'City'),
            'city_fias_id' => Yii::t('app', 'City Fias ID'),
            'street' => Yii::t('app', 'Street'),
            'street_fias_id' => Yii::t('app', 'Street Fias ID'),
            'house' => Yii::t('app', 'House'),
            'flat' => Yii::t('app', 'Flat'),
            'housing' => Yii::t('app', 'Housing'),
            'building' => Yii::t('app', 'Building'),
            'postcode' => Yii::t('app', 'Postcode'),
            'full_address' => Yii::t('app', 'Full Address'),
            'address_object' => Yii::t('app', 'Address Object'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouses()
    {
        return $this->hasMany(Warehouse::className(), ['address_id' => 'id']);
    }

    /**
     * @param string $addressString
     * @return Address|null
     */
    public function getPerformed(string $addressString = ''): ?Address
    {
        if ($addressString == '') {
            $addressParams = [];
            if ($this->region) {
                $addressParams[] = $this->region;
            }
            if ($this->city) {
                $addressParams[] = $this->city;
            }
            if ($this->street) {
                $addressParams[] = (strripos($this->street,  'ул. ') ===  false) ? 'ул. ' . $this->street : $this->street;
            }
            if ($this->house && !in_array($this->house, self::FORBIDDEN_SYMBOLS)) {
                $addressParams[] = (strripos($this->house,  'д. ') ===  false) ? 'д. ' . $this->house : $this->house;
            }
            if ($this->building) {
                $addressParams[] = (strripos($this->building,  'стр. ') ===  false) ? 'стр. ' . $this->building : $this->building;
            }
            if ($this->housing) {
                $addressParams[] = (strripos($this->housing,  'к. ') ===  false) ? 'к. ' . $this->housing : $this->housing;
            }
            if ($this->flat) {
                $addressParams[] = (strripos($this->flat,  'кв. ') ===  false) ? 'кв. ' . $this->flat : $this->flat;
            }
            $addressString = implode(', ', $addressParams);
        }

        $suggestions = (new Dadata())->getSuggestions('address', [
            'query' => $addressString,
            'limit' => 1
        ]);

        if (!empty($suggestions['suggestions'])) {

            $suggestion = (new \app\api\builder\Suggestion($suggestions['suggestions'][0]))->getSuggestion();

            $address = new Address();
            $address->region = $suggestion->data->regionWithType;
            $address->region_fias_id = $suggestion->data->regionFiasId;
            $address->street = $suggestion->data->streetWithType;
            $address->street_fias_id = $suggestion->data->streetFiasId;
            $address->house = $suggestion->data->house;
            $address->housing = $suggestion->data->block;
            $address->flat = $suggestion->data->flat;
            $address->full_address = $suggestion->unrestrictedValue;
            $address->postcode = $suggestion->data->postalCode;
            $address->lng = $suggestion->data->geoLon;
            $address->lat = $suggestion->data->geoLat;

            $address->city = $suggestion->data->cityWithType;
            $address->city_fias_id = $suggestion->data->cityFiasId;

            if (!$suggestion->data->cityWithType && $suggestion->data->settlementWithType) {
                $address->city = $suggestion->data->settlementWithType;
                $address->city_fias_id = $suggestion->data->settlementFiasId;
            } elseif ($suggestion->data->cityWithType && $suggestion->data->settlementWithType && (
                $suggestion->data->settlementType != 'мкр'
                    && $suggestion->data->settlementType != 'тер'
                    && $suggestion->data->settlementType != 'кп'
                    && $suggestion->data->settlementType != 'р-н'
                    && $suggestion->data->settlementType != 'жилрайон'
                    && $suggestion->data->settlementTypeFull != 'поселок'
                )
            ) {
                $address->city = $suggestion->data->settlementWithType;
                $address->city_fias_id = $suggestion->data->settlementFiasId;
            }

            return $address;
        }
        return null;
    }
}
