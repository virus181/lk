<?php
namespace app\models;

use app\behaviors\LogBehavior;
use app\rbac\AccessControl;
use Yii;
use yii\base\Security;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;
use yii\rbac\Role;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $access_token
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $fio
 * @property int $internal_number
 * @property string $location
 * @property int $group_id
 * @property string $auth_key
 * @property integer $status
 * @property boolean $notify
 * @property array $statuses
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password
 * @property Shop[] $shops
 * @property Warehouse[] $warehouses
 */
class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_SELF_UPDATE = 'selfUpdate';
    const SCENARIO_CREATE = 'create';

    const STATUS_DELETED = 0;
    const STATUS_SYSTEM = 1;
    const STATUS_ACTIVE = 10;

    const ROLE_WATCHER = 'shopWatcher';
    const ROLE_SYSTEM = 'systemAdmin';
    const ROLE_ROOT = 'root';
    const ROLE_ADMIN = 'shopAdmin';

    private $_password = '';
    private $role;
    private $shopIds = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public function resetAccessToken()
    {
        $this->access_token = $this->generateAccessToken();
    }

    public function generateAccessToken()
    {
        return (new Security())->generateRandomString(32);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array(
            TimestampBehavior::className(),
            LogBehavior::className(),
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['password', 'default', 'value' => (new Security())->generateRandomString(8), 'on' => self::SCENARIO_CREATE],
            ['access_token', 'default', 'value' => $this->generateAccessToken()],
            ['notify', 'default', 'value' => true],
            ['password', 'string', 'min' => 8],
            ['role', 'default', 'value' => 'shopAdmin', 'on' => self::SCENARIO_CREATE],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['auth_key', 'default', 'value' => $this->generateAuthKey()],
            [['fio', 'email', 'role'], 'required'],
            [['password'], 'required', 'on' => self::SCENARIO_CREATE],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED], 'on' => self::SCENARIO_CREATE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_SYSTEM, self::STATUS_DELETED], 'on' => self::SCENARIO_DEFAULT],
            [['status', 'notify'], 'integer'],
            ['fio', 'string', 'max' => 512],
            ['access_token', 'string', 'length' => 32],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [['internal_number', 'group_id'], 'number'],
            [['location'], 'string', 'max' => 32],
            ['email', 'unique', 'targetClass' => User::className(), 'message' => Yii::t('app', 'This email address has already been taken')],
            ['shopIds', 'default', 'value' => []],
            ['shopIds', 'validateShopIds', 'skipOnEmpty' => true, 'skipOnError' => false],
            [['email', 'fio'], 'trim']
        ];

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $roles = $user->getAllowedRoleIds();
            $shopIds = $user->getAllowedShopIds();

            $rules[] = ['role', 'in', 'range' => $roles];

            if ($shopIds !== []) {
                $rules[] = ['shopIds', 'each', 'rule' => ['in', 'range' => $shopIds]];
            }
        }

        return $rules;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        return Yii::$app->security->generateRandomString();
    }

    /**
     * @return Role[]
     */
    public function getAllowedRoleIds()
    {

        return array_keys($this->getAllowedRoles());
    }

    /**
     * @return Role[]
     */
    public function getAllowedRoles()
    {
        /** @var DbManager $authManager */
        $authManager = Yii::$app->authManager;
        $currentRoles = $authManager->getRolesByUser(Yii::$app->user->id);
        $roles = [];

        $this->collectChildRoles($currentRoles, $roles);

        return $roles;
    }

    /**
     * @param Role[] $currentRoles
     * @param $roles array
     */
    private function collectChildRoles($currentRoles, &$roles)
    {
        /** @var DbManager $authManager */
        $authManager = Yii::$app->authManager;
        /** @var Role $currentRole */
        foreach ($currentRoles as $currentRole) {
            if (!isset($roles[$currentRole->name])) {
                $roles[$currentRole->name] = ($currentRole->description) ? $currentRole->description : $currentRole->name;

                if ($childRoles = $authManager->getChildRoles($currentRole->name)) {
                    $this->collectChildRoles($childRoles, $roles);
                }
            }
        }
    }

    /**
     * @return array|bool
     */
    public function getAllowedShopIds()
    {
        $userShopIds = [];
        if (AccessControl::can('/shop/*') === false) {
            $userShopIds = ArrayHelper::getColumn(
                $this->getShops()->select('id')->where(['status' => Shop::STATUS_ACTIVE])->asArray()->all(),
                'id'
            );
            if (!$userShopIds) {
                $userShopIds = false;
            }
        }

        return $userShopIds;
    }

    /**
     * Досутпен ли магазин для данного пользователя
     * @param int $shopId
     * @return bool
     */
    public function isShopAvailableForUser(int $shopId): bool
    {
        $availableShopIds = $this->getAllowedShopIds();

        if ($availableShopIds === false) {
            return false;
        }

        if (!empty($availableShopIds)) {
            return in_array($shopId, $availableShopIds);
        }

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShops()
    {
        return $this->hasMany(Shop::className(), ['id' => 'shop_id'])->viaTable('{{%user_shop}}', ['user_id' => 'id']);
    }

    /**
     * Возвращает список доступных ID складов
     * @return array|bool
     */
    public function getAllowedWarehouseIds()
    {
        return Yii::$app->cache->getOrSet('allowedWarehouseForUser' . $this->id, function ()
        {
            $result = [];
            if (AccessControl::can('/warehouse/*') === false) {

                $shopWarehouseIds = ArrayHelper::getColumn(
                    Shop::find()
                        ->select('warehouse.id')
                        ->joinWith(['warehouses'])
                        ->where(['shop.id' => $this->getAllowedShopIds()])
                        ->asArray()
                        ->all(),
                    'id'
                );

                $userWarehouseIds = ArrayHelper::getColumn(
                    $this->getWarehouses()
                        ->select('id')
                        ->asArray()
                        ->all(),
                    'id'
                );

                $warehouseIds = array_merge($shopWarehouseIds, $userWarehouseIds);

                if (empty($warehouseIds)) {
                    $result = false;
                } else {
                    $result = [];
                    foreach ($warehouseIds as $id) {
                        if (!empty($id) && !in_array($id, $result)) {
                            $result[] = $id;
                        }
                    }
                }
            }

            return $result;
        }, 3600);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouses()
    {
        return $this->hasMany(Warehouse::className(), ['id' => 'warehouse_id'])->viaTable('{{%user_warehouse}}', ['user_id' => 'id']);
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

        $fields['status'] = function () {
            $statuses = $this->getStatuses();
            return mb_convert_case($statuses[$this->status], MB_CASE_TITLE, "UTF-8");
        };

        $fields['password'] = function () {
            return $this->password;
        };

        $fields['roles'] = function () {
            $rolesNames = [];
            $roles = Yii::$app->authManager->getRolesByUser($this->id);
            foreach ($roles as $key => $role) {
                $rolesNames[] = [$key => $role->description];
            }
            return $rolesNames;
        };

        unset($fields['access_token'], $fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

        return $fields;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return $statuses = [
            self::STATUS_ACTIVE => Yii::t('app', 'active'),
            self::STATUS_DELETED => Yii::t('app', 'deleted'),
        ];
    }

    /**
     * @return array
     */
    public static function getStatusList()
    {
        return $statuses = [
            self::STATUS_ACTIVE => Yii::t('app', 'active'),
            self::STATUS_DELETED => Yii::t('app', 'deleted'),
        ];
    }

    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields[] = 'shops';

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'email' => Yii::t('app', 'Email'),
            'fio' => Yii::t('app', 'Fio'),
            'notify' => Yii::t('app', 'Notify'),
            'status' => Yii::t('app', 'Status'),
            'shopIds' => Yii::t('app', 'Allowed Shops'),
            'internal_number' => Yii::t('user', 'Internal number'),
            'location' => Yii::t('user', 'Location'),
            'group_id' => Yii::t('user', 'Group ID'),
            'role' => Yii::t('app', 'Role'),
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SELF_UPDATE] = ['fio', 'email', 'notify'];
        $scenarios[self::SCENARIO_CREATE] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    public function validateShopIds($attribute)
    {
        if ($this->role !== 'admin' && $this->role !== 'root' && empty($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Select allowed shops for selected user role'));
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @return bool
     */
    public function setPassword($password)
    {
        $this->_password = $password;
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        return true;
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @param null $statusCode
     * @return string
     */
    public function getStatusName($statusCode = null)
    {
        return ArrayHelper::getValue($this->statuses, $statusCode ? $statusCode : $this->status);
    }

    public function getShopIds()
    {
        if (!$this->shopIds) {
            $this->shopIds = ArrayHelper::getColumn($this->getShops()->select('id')->asArray()->all(), 'id');
        }
        return $this->shopIds;
    }

    public function setShopIds($shopIds)
    {
        $this->shopIds = $shopIds;
    }

    public function getRole()
    {
        if (!$this->role && ($role = Yii::$app->authManager->getRolesByUser($this->id))) {
            $this->role = array_keys($role)[0];
        }

        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * Получить список дотсупных магазинов для пользователя
     *
     * @return array
     */
    public function getAllowedShops(): array
    {
        return Yii::$app->cache->getOrSet('user_' . $this->getId() . '_shops', function () {
            return ArrayHelper::map(
                Shop::find()
                    ->andFilterWhere([
                        'id' => $this->getAllowedShopIds(),
                        'status' => Shop::STATUS_ACTIVE
                    ])
                    ->asArray()
                    ->orderBy(['name' => SORT_ASC])
                    ->all(),
                'id',
                'name'
            );
        }, Helper::MIN_CACHE_VALUE);
    }

    /**
     * Отправка письма пользователю
     *
     * @return bool
     */
    public function sendNewUserEmail(): bool
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'newUser-html', 'text' => 'newUser-text'],
                ['user' => $this]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Your new access in ' . Yii::$app->name)
            ->send();
    }

    /**
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return isset($this->getStatuses()[$this->status]) ? $this->getStatuses()[$this->status] : null;
    }

    /**
     * @return string
     */
    public function getFio(): string
    {
        return $this->fio;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}