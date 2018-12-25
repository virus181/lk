<?php
namespace app\models;

use app\delivery\DeliveryHelper;
use app\models\queries\DeliveryServiceQuery;
use app\models\queries\RegistryQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%delivery_service}}".
 *
 * @property int $id
 * @property string $full_name
 * @property string $name
 * @property string $region
 * @property string $city_fias_id
 * @property int $owner_id
 * @property string $code
 *
 * @property Delivery $delivery
 */
class CityPickPoint extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city_pp}}';
    }

    /**
     * @inheritdoc
     * @return DeliveryServiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DeliveryServiceQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'owner_id'], 'integer'],
            [['full_name', 'name', 'city_fias_id', 'region', 'code'], 'string'],
        ];
    }
}
