<?php

namespace app\models;

use app\behaviors\LogBehavior;
use app\models\queries\CallQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%call}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $shop_id
 * @property string $key
 * @property string $direction
 * @property int $operator_id
 * @property int $user_id
 * @property string $operator_name
 * @property int $call_id
 * @property string $answer_time
 * @property string $ring_time
 * @property string $end_time
 * @property string $close_time
 * @property string $rec_uid
 * @property string $uid
 * @property string $shop_phone
 * @property string $client_phone
 * @property string $tag
 * @property string $note
 * @property int $created_at
 * @property int $updated_at
 * @property string $call_time
 *
 * @property Shop $shop
 * @property User $user
 * @property Order $order
 */
class Call extends ActiveRecord
{
    const DOWNLOAD_URL = 'https://asterisk.fidoman.ru/cgi-bin/data.py?what=get_rec&code=%s';

    const CALL_STATUS_LIST = [
        '0' => 'Empty',
        '1' => 'Ok',
    ];

    const TAG_SUCCESS = 1;
    const TAG_RESALE = 2;
    const TAG_NO_ANSWER = 3;
    const TAG_BAD_CONNECTION = 4;
    const TAG_RECALL_IN_30_MIN = 5;
    const TAG_RECALL_IN_3_HOURS = 6;
    const TAG_RECALL_IN_1_DAY = 7;
    const TAG_WITHOUT_RECORD = 8;
    const TAG_TRANSFER = 9;

    const TAG_MAPPER = [
        self::TAG_SUCCESS => 'Success',
        self::TAG_RESALE => 'Resale',
        self::TAG_NO_ANSWER => 'No answer',
        self::TAG_BAD_CONNECTION => 'Bad connection',
        self::TAG_RECALL_IN_30_MIN => 'Recall in 30 minutes',
        self::TAG_RECALL_IN_3_HOURS => 'Recall in 3 hours',
        self::TAG_RECALL_IN_1_DAY => 'Recall tomorrow',
        self::TAG_WITHOUT_RECORD => 'Without record',
        self::TAG_TRANSFER => 'Call transfer',
    ];

    const INCOMING = 'incoming';
    const OUTGOING = 'outgoing';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%call}}';
    }

    /**
     * @inheritdoc
     * @return CallQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CallQuery(get_called_class());
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
            [['shop_id'], 'required'],
            [['shop_id', 'order_id', 'operator_id', 'call_id', 'user_id'], 'number'],
            [['shop_phone', 'client_phone', 'direction'], 'string', 'max' => 32],
            [['key', 'operator_name', 'answer_time', 'ring_time', 'end_time', 'close_time', 'rec_uid', 'uid', 'tag'], 'string', 'max' => 256],
            [['note'], 'string']
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
            'shop_id' => Yii::t('app', 'Shop ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'key' => Yii::t('app', 'Key'),
            'direction' => Yii::t('app', 'Direction call'),
            'operator_id' => Yii::t('app', 'Operator ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'operator_name' => Yii::t('app', 'Operator name'),
            'call_id' => Yii::t('app', 'Call ID'),
            'answer_time' => Yii::t('app', 'Answer time'),
            'ring_time' => Yii::t('app', 'Ring time'),
            'end_time' => Yii::t('app', 'End time'),
            'close_time' => Yii::t('app', 'Close time'),
            'call_time' => Yii::t('call', 'Call time'),
            'rec_uid' => Yii::t('app', 'Record ID'),
            'uid' => Yii::t('app', 'UID'),
            'shop_phone' => Yii::t('app', 'Shop phone'),
            'client_phone' => Yii::t('app', 'Client phone'),
            'tag' => Yii::t('app', 'Tag'),
            'created_at' => Yii::t('app', 'Created at'),
            'updated_at' => Yii::t('app', 'Updated at'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getRingDate(): string
    {
        return date('Y-m-d, H:i', strtotime($this->ring_time));
    }

    /**
     * @return int|null
     */
    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    /**
     * @return string
     */
    public function getOperatorName(): ?string
    {
        return $this->user_id ? $this->user->fio : $this->operator_name;
    }

    /**
     * @return string
     */
    public function getClientPhone(): string
    {
        return (new Order())->getNormalizePhone($this->client_phone);
    }

    /**
     * @return string
     */
    public function getShopPhone(): string
    {
        return (new Order())->getNormalizePhone($this->shop_phone);
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return Html::tag('span', Yii::t('app', (string) $this->direction), ['class' => 'td-span ' . (string) $this->direction]);
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return Html::tag(
            'span',
            Yii::t('app', self::TAG_MAPPER[$this->tag]),
            ['class' => 'td-span tag-' . $this->tag]
        );
    }

    /**
     * @return string
     */
    public function getShopName(): string
    {
        return $this->shop->name;
    }


    /**
     * @return string
     */
    public function getStatusCall(): string
    {
        if ($this->rec_uid) {
            return Html::tag(
                'div',
                Yii::t('app', 'Ok'),
                ['class' => 'label label-success']
            );
        }
        return Html::tag(
            'div',
            Yii::t('app', 'Empty'),
            ['class' => 'label label-default']
        );
    }

    /**
     * @return string
     */
    public function getDownloadUrl(): ?string
    {
        return $this->rec_uid
            ? Html::a(
                '<i class="fa fa-download"></i>',
                sprintf(self::DOWNLOAD_URL, $this->key),
                ['class' => 'btn btn-xs btn-default', 'target' => '_blank']
            )
            : null;
    }

    public static function getTagList(): array
    {
        $array = [];
        foreach (self::TAG_MAPPER as $key => $value) {
            $array[$key] = Yii::t('app', $value);
        }
        return $array;
    }

    public static function getDirectionList(): array
    {
        return [
            self::INCOMING => Yii::t('app', self::INCOMING),
            self::OUTGOING => Yii::t('app', self::OUTGOING)
        ];
    }

    public function getClientName(): string
    {
        return $this->order ? $this->order->fio : 'Неизвестный';
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getCallTime(): string
    {
        if (!$this->answer_time) {
            return '---';
        }
        $ringSeconds = strtotime($this->end_time) - strtotime($this->answer_time);
        if ($ringSeconds > 60) {
            $min = (int) ($ringSeconds / 60);
            $sec = $ringSeconds % 60;
            return sprintf('%d мин. %d сек.', $min, $sec);
        }
        return sprintf('%d сек.', $ringSeconds);
    }
}
