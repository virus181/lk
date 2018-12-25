<?php

namespace app\models;

use app\models\queries\PointsQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%points}}".
 *
 * @property int $id
 * @property int $point_id
 * @property string $name
 * @property string $code
 * @property string $carrier_key
 * @property string $additional_code
 * @property int $cod
 * @property int $type
 * @property int $available_operation
 * @property int $card
 * @property string $address
 * @property string $city_guid
 * @property string $phone
 * @property string $timetable
 * @property string $lat
 * @property string $lng
 * @property string $class_name
 * @property int $created_at
 * @property int $updated_at
 */
class Points extends ActiveRecord
{
    const OPERATION_TYPE_RECEIVE = 1;
    const OPERATION_TYPE_DELIVERY = 2;
    const OPERATION_TYPE_RECEIVE_AND_DELIVERY = 3;

    const POINT_TYPE_PVZ = 1;
    const POINT_TYPE_POSTAMAT = 2;
    const POINT_TYPE_MAIL = 3;
    const POINT_TYPE_TERMINAL = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%points}}';
    }

    /**
     * @param $className string
     * @return static[]
     */
    public static function findByClassName($className)
    {
        return self::findAll(['className' => $className]);
    }

    /**
     * @inheritdoc
     * @return PointsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PointsQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'address', 'phone', 'timetable', 'class_name', 'lat', 'lng'], 'string'],
            [['code', 'additional_code', 'address', 'type', 'available_operation', 'point_id', 'carrier_key'], 'required'],
            [['cod', 'card', 'type', 'operation_type', 'point_id'], 'integer'],
            [['code', 'additional_code', 'city_guid', 'carrier_key'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'code' => Yii::t('app', 'Code'),
            'additional_code' => Yii::t('app', 'Additional Code'),
            'cod' => Yii::t('app', 'Cod'),
            'card' => Yii::t('app', 'Card'),
            'address' => Yii::t('app', 'Address'),
            'city_guid' => Yii::t('app', 'City Guid'),
            'phone' => Yii::t('app', 'Phone'),
            'timetable' => Yii::t('app', 'Timetable'),
            'lat' => Yii::t('app', 'Lat'),
            'lng' => Yii::t('app', 'Lng'),
            'class_name' => Yii::t('app', 'Class Name'),
        ];
    }
}
