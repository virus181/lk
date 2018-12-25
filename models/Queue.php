<?php

namespace app\models;

use app\models\queries\QueueQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%queue}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 */
class Queue extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%queue}}';
    }

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     * @return QueueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new QueueQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'model_id', 'status', 'sort', 'created_at', 'updated_at'], 'integer'],
            [['model', 'action'], 'string', 'max' => 255],
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
            'model_id' => Yii::t('app', 'Model ID'),
            'model' => Yii::t('app', 'Model'),
            'action' => Yii::t('app', 'Action'),
            'status' => Yii::t('app', 'Status'),
            'sort' => Yii::t('app', 'Sort'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
