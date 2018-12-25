<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%message}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $text
 * @property string $fio
 * @property string $phone
 * @property int $type
 * @property string $file
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class Message extends ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $imageFile;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%message}}';
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

        $rules = [
            [['phone'], 'filter', 'filter' => function () {
                return '+7' . (new Order())->getClearPhone($this->phone);
            }],
            [['user_id'], 'default', 'value' => function() {
                return Yii::$app->user->identity->getId();
            }],
//            [
//                ['phone'],
//                'string',
//                'notEqual' => Yii::t('app', 'Значение «Телефон» должно содержать 11 цифр и начинаться с 7, +7, 8 или 9'),
//                'length' => 12
//            ],
            [['fio', 'phone', 'user_id', 'type', 'text', 'title'], 'required'],
            [['type', 'user_id'], 'integer'],
            [
                ['type'],
                'compare',
                'compareValue' => 0,
                'operator' => '>',
                'message' => Yii::t('app', 'Choose type of message')
            ],
            [['title', 'fio'], 'string', 'max' => 255],
            [['text'], 'string'],
            [['file'], 'string'],
            [['imageFile'], 'image', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, gif', 'maxSize'=>1024*5000],
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'type' => Yii::t('app', 'Message type'),
            'title' => Yii::t('app', 'Title'),
            'fio' => Yii::t('app', 'Contact Fio'),
            'phone' => Yii::t('app', 'Contact Fio'),
            'text' => Yii::t('app', 'Message text'),
            'file' => Yii::t('app', 'Изображение'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    /**
     * @return array
     */
    public static function getMessageTypes()
    {
        return [
            0 => Yii::t('app', 'Choose'),
            1 => Yii::t('app', 'Error message'),
            2 => Yii::t('app', 'Consultation message'),
            3 => Yii::t('app', 'Order information message'),
            4 => Yii::t('app', 'Accounting message'),
            5 => Yii::t('app', 'Integration problem message')
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app
            ->mailer
            ->compose(
                ['html' => 'message-html'],
                ['message' => $this]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo(Yii::$app->params['messageEmail'])
            ->setSubject('Новое сообщение из раздела Поддержка')
            ->send();

        parent::afterSave($insert, $changedAttributes);
    }

    public function upload($path)
    {
        if ($this->validate('imageFile')) {
            $this->imageFile->saveAs($path . $this->imageFile->baseName . '.' . $this->imageFile->extension);
            return true;
        } else {
            return false;
        }
    }
}
