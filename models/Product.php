<?php

namespace app\models;

use app\behaviors\LogBehavior;
use app\models\queries\ProductQuery;
use app\models\search\ProductSearch;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%product}}".
 *
 * @property int $id
 * @property int $additional_id
 * @property string $name
 * @property string $barcode
 * @property double $price
 * @property double $accessed_price
 * @property double $weight
 * @property int $shop_id
 * @property int $count
 * @property int $status
 * @property int $width
 * @property int $length
 * @property int $height
 * @property boolean $is_not_reversible
 * @property int $created_at
 * @property int $updated_at
 *
 * @property OrderProduct[] $orderProducts
 * @property Order[] $orders
 * @property Shop $shop
 */
class Product extends ActiveRecord
{
    const SCENARIO_MANUAL_CREATE = 'MANUAL_CREATE';
    const SCENARIO_API_CREATE = 'API_CREATE';
    const SCENARIO_PRODUCT_LIST_API = 'PRODUCT_LIST_API';
    const SCENARIO_CREATE_ORDER = 'CREATE_ORDER';
    const SCENARIO_CREATE_ORDER_LK = 'CREATE_ORDER_LK';

    const STATUS_ACTIVE = 10;
    const STATUS_BLOCKED = 0;

    public $quantity;
    public $min_price;
    public $max_price;
    public $in_stock;
    public $ids;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @inheritdoc
     * @return ProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProductQuery(get_called_class());
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
        $shopIds = false;
        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $shopIds = $user->getAllowedShopIds();
        }

        $rules = [
            [['weight'], 'default', 'value' => 10],
            [['count', 'price'], 'default', 'value' => 0, 'on' => self::SCENARIO_DEFAULT],
            [['count', 'price'], 'default', 'value' => 0, 'on' => self::SCENARIO_API_CREATE],
            [['count', 'price'], 'default', 'value' => 0, 'on' => self::SCENARIO_CREATE_ORDER],
            [['count', 'price'], 'default', 'value' => 0, 'on' => self::SCENARIO_CREATE_ORDER_LK],
            [['quantity'], 'default', 'value' => 1],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['accessed_price'], 'default', 'value' => function () {
                return $this->price ?? 1;
            }],
            [['shop_id'], 'required', 'on' => self::SCENARIO_PRODUCT_LIST_API],
            [['shop_id'], 'required', 'on' => self::SCENARIO_API_CREATE],
            [['min_price', 'max_price'], 'integer', 'min' => 0,  'on' => self::SCENARIO_PRODUCT_LIST_API],
            [['in_stock'], 'boolean', 'on' => self::SCENARIO_PRODUCT_LIST_API],
            [['is_not_reversible'], 'boolean'],
            [['barcode'], 'default', 'value' => ''],
            [['name', 'price', 'weight', 'count'], 'required'],
            [['weight', 'width', 'height', 'length'], 'number'],
            [['price', 'accessed_price'], 'double'],
            [['ids'], 'each', 'rule' => ['integer']],
            [['ids'], 'safe'],
            ['accessed_price', 'validateAccessedPrice', 'skipOnEmpty' => true, 'skipOnError' => false],
            [
                ['accessed_price'],
                'compare',
                'compareValue' => 0,
                'operator' => '>',
                'message' => Yii::t('app', 'Accessed price must be greater than zero')
            ],
            [['weight'], 'number', 'min' => 1, 'on' => self::SCENARIO_CREATE_ORDER],
            [['quantity'], 'integer', 'min' => 1],
            [['status'], 'integer'],
            [['name'], 'string', 'max' => 1024],
            [['barcode'], 'string', 'max' => 255],
            [['barcode', 'name'], 'trim'],
            [['!shop_id', 'additional_id', 'count', 'quantity'], 'integer'],
            [['!shop_id'], 'validateProduct', 'skipOnError' => true, 'on' => self::SCENARIO_CREATE_ORDER_LK],
            [['!shop_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shop::className(), 'targetAttribute' => ['shop_id' => 'id']],
            [['barcode'],
                'unique',
                'targetClass' => Product::className(),
                'targetAttribute' => ['barcode', 'shop_id'],
                'message' => Yii::t('app', 'Barcode must be unique for current shop'),
                'on' => self::SCENARIO_API_CREATE
            ],

        ];

        if ($shopIds !== []) {
            $rules[] = [['!shop_id'], 'in', 'range' => ($shopIds === false ? [] : $shopIds)];
        }

        return $rules;
    }

    /**
     * Проверка оценочной стоимости товара
     * @param $attribute
     */
    public function validateAccessedPrice($attribute)
    {
        if (!empty($this->price) && $this->price != 0 && $this->{$attribute} > $this->price) {
            $this->addError($attribute, Yii::t('app', 'Accessed price must be lower or equal price'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'additional_id' => Yii::t('app', 'Additional ID'),
            'name' => Yii::t('app', 'Name'),
            'barcode' => Yii::t('app', 'Barcode'),
            'price' => Yii::t('app', 'Price'),
            'accessed_price' => Yii::t('app', 'Accessed Price'),
            'weight' => Yii::t('app', 'Weight'),
            'shop_id' => Yii::t('app', 'Shop'),
            'count' => Yii::t('app', 'Count'),
            'status' => Yii::t('app', 'Status'),
            'quantity' => Yii::t('app', 'Quantity'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
            'length' => Yii::t('app', 'Length'),
            'is_not_reversible' => Yii::t('app', 'Is not reversible'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['id' => 'order_id'])->viaTable('{{%order_product}}', ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * Правило проверки товара на наличие в Складской системе
     * @param $attribute
     */
    public function validateProduct($attribute)
    {
        if (($this->shop_id && $this->barcode) || $this->id) {
            // TODO придумать более элегантный вариант проверки
            $query = Product::find()
                ->select([
                    'product.id',
                    'product.count',
                    'product.additional_id',
                    'shop.fulfillment'
                ])
                ->joinWith(['shop']);

            if ($this->id) {
                $query->where(['product.id' => $this->id]);
            } elseif ($this->shop_id && $this->barcode) {
                $query->where([
                    'product.shop_id' => $this->shop_id,
                    'product.barcode' => $this->barcode
                ]);
            }

            $product = $query->one();

            if ((!empty($this->shop) && $this->shop->fulfillment)
                && $product
                && (!is_null($product->additional_id) && $product->count == 0)
            ) {
                $this->addError($attribute, Yii::t('app', 'Product are not in the storage system'));
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

        $fields['stored'] = function () {
            return (int)(bool)$this->additional_id;
        };

        $fields['accessed_price'] = function () {
            return !is_null($this->accessed_price) ? $this->accessed_price : $this->price;
        };

        //TODO: Разобраться с кол-вом товаров
        unset($fields['count']);
        if ($this->quantity !== null) {
            $fields['quantity'] = function () {
                return (int)$this->quantity;
            };
        }

        $fields['in_stock'] = function () {
            return $this->count;
        };

        unset($fields['additional_id']);

        return $fields;
    }

    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields[] = 'shop';
        $fields[] = 'orders';
        $fields[] = 'orderProducts';

        return $fields;
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_MANUAL_CREATE] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_API_CREATE] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_PRODUCT_LIST_API] = ['shop_id', 'min_price', 'max_price', 'in_stock', 'ids'];
        return $scenarios;
    }

    public function beforSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->quantity = null;
            return true;
        }

        return false;
    }

    /**
     * Действия с моделью Product после нахождения модели
     */
    public function afterFind()
    {
        if (is_null($this->accessed_price)) {
            $this->accessed_price = $this->price;
        }
        parent::afterFind();
    }

    /**
     * Получение всех статусов продукта
     *
     * @return array
     */
    public static function getProductStatuses()
    {
        return [
            self::STATUS_ACTIVE => Yii::t('app', 'active'),
            self::STATUS_BLOCKED => Yii::t('app', 'blocked')
        ];
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return date(ProductSearch::DATE_FORMAT, $this->created_at);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getBarcode(): string
    {
        return $this->barcode;
    }

    /**
     * @return string
     */
    public function getCount(): string
    {
        return $this->count;
    }

    /**
     * @param bool $formatted
     * @return string
     */
    public function getPrice($formatted = true): string
    {
        return $formatted ? Yii::$app->formatter->asCurrency($this->price, 'RUB') : $this->price;
    }

    /**
     * @param bool $formatted
     * @return string
     */
    public function getAccessedPrice($formatted = true): string
    {
        return $formatted ? Yii::$app->formatter->asCurrency($this->accessed_price, 'RUB') : $this->accessed_price;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight / 1000;
    }

    /**
     * @return string
     */
    public function getShopName(): string
    {
        return $this->shop->name;
    }
}
