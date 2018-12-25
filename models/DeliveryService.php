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
 * @property int $delivery_id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property int $status
 * @property string $service_key
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Delivery $delivery
 */
class DeliveryService extends ActiveRecord
{
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 10;

    const SERVICE_PARTIAL = 'partial';
    const SERVICE_CLIENT_NUMBER = 'clientNumber';

    const EXTRA_PARAM_ADD_VALUE = "1";
    const EXTRA_PARAM_REMOVE_VALUE = "0";

    const SERVICES = [
        'b2cpl.packaging' => 'packaging',
        'b2cpl.partialrefusing' => 'partialRefusing',
        'cdek.DeliveryInWeekend' => 'deliveryInWeekend',
        'cdek.PickUpInSenderCity' => 'pickUpInSenderCity',
        'cdek.DeliveryInRecipientCity' => 'deliveryInRecipientCity',
        'cdek.FittingAtHome' => 'fitting',
        'cdek.PartialDelivery' => self::SERVICE_PARTIAL,
        'cdek.InspectionAttachments' => 'inspecting',
        'iml.PartialDelivery' => self::SERVICE_PARTIAL,
        'iml.InspectionAttachments' => 'inspecting',
        'iml.Fitting' => 'fitting',
        'iml.CodeConfirmation' => 'codeConfirmation',
        'pickpoint.ClientNumber' => self::SERVICE_CLIENT_NUMBER,
    ];

    // Mapa значений для доп параметров, нужно подумать как сделать более красиво
    const DELIVERY_SERVICE_KEY_MAP = [
        DeliveryHelper::CARRIER_CODE_PICKPOINT => [
            self::SERVICE_CLIENT_NUMBER => [
                348 => 'shop.vitamax',
                349 => 'shop.vitamax',
            ]
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery_service}}';
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
    public function behaviors()
    {
        return array(
            TimestampBehavior::className(),
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delivery_id', 'name', 'service_key'], 'required'],
            [['status', 'delivery_id'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['name', 'description', 'service_key'], 'string'],
            [['type'], 'safe'],
            [['delivery_id'], 'exist', 'skipOnError' => true, 'targetClass' => Delivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('delivery', 'ID'),
            'delivery_id' => Yii::t('delivery', 'Delivery ID'),
            'name' => Yii::t('delivery', 'Name'),
            'description' => Yii::t('delivery', 'Description'),
            'type' => Yii::t('delivery', 'Type'),
            'status' => Yii::t('delivery', 'Status'),
            'service_key' => Yii::t('delivery', 'Service key'),
            'created_at' => Yii::t('delivery', 'Created at'),
            'updated_at' => Yii::t('delivery', 'Updated at'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery()
    {
        return $this->hasOne(Delivery::className(), ['id' => 'delivery_id']);
    }

    /**
     * @param string $code
     * @return string|null
     */
    public function getService(string $code): ?string
    {
        return (isset(self::SERVICES[$code])) ? self::SERVICES[$code] : null;
    }
}
