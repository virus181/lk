<?php

namespace app\models;

use app\delivery\DeliveryHelper;
use app\models\queries\RegistryQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%log}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $model_id
 * @property int $owner_id
 * @property string $model
 * @property string $attribute
 * @property string $old_value
 * @property string $value
 * @property string $data
 * @property int $is_new
 * @property int $user_ip
 * @property string $denorm
 * @property int $created_at
 * @property int $updated_at
 */
class Log extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%log}}';
    }

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     * @return RegistryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RegistryQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'model_id', 'owner_id', 'is_new'], 'integer'],
            [['model', 'attribute'], 'string', 'max' => 255],
            [['old_value', 'value', 'data', 'denorm', 'user_ip'], 'string'],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'model_id' => Yii::t('app', 'Model ID'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'model' => Yii::t('app', 'Model'),
            'data' => Yii::t('app', 'Data'),
            'is_new' => Yii::t('app', 'Is new'),
            'user_ip' => Yii::t('app', 'User IP'),
            'denorm' => Yii::t('app', 'Denormalization data'),
            'attribute' => Yii::t('app', 'Attribute'),
            'old_value' => Yii::t('app', 'Old value'),
            'value' => Yii::t('app', 'Value'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param array $ownerIds
     * @return ActiveDataProvider
     */
    public function search($params, $ownerIds = [])
    {
        $query = Log::find()->joinWith(['user']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (empty($ownerIds)) {
            $query->andFilterWhere([
                'log.owner_id' => $this->owner_id,
            ]);
        } else {
            $query->andFilterWhere([
                'IN', 'log.owner_id', $ownerIds
            ]);
        }


        $query->andFilterWhere([
            'IN', 'model', ['Order', 'OrderDelivery', 'OrderProduct', 'OrderMessage', 'Address']
        ]);

        return $dataProvider;
    }


    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param string $attribute
     * @param string $data
     * @return string
     */
    public static function prepareData(string $attribute, string $data): string
    {
        switch ($attribute) {
            case 'phone':
                return (new \app\models\Helper\Phone($data))->getHumanView();
            case 'is_api':
                return ($data) ? 'Да' : 'Нет';
            case 'payment_method':
                return Helper::getPaymentMethodName($data);
            case 'status':
                return (new Order())->getWorkflowStatusName($data);
            case 'type':
                return Helper::getDeliveryTypeName($data);
            case 'pickup_date':
                return date('d.m.Y', $data);
            case 'delivery_date':
                return date('d.m.Y', $data);
            case 'carrier_key':
                return DeliveryHelper::getName($data);
            case 'point_type':
                return Yii::t('delivery', $data);
            default:
                return $data;
        }
    }

}
