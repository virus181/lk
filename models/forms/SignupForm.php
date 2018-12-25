<?php
namespace app\models\forms;

use app\models\User;
use Yii;
use yii\base\Model;
use yii\rbac\DbManager;
use yii\rbac\Role;

class SignupForm extends Model
{
    public $email;
    public $fio;
    public $password;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fio', 'email', 'password'], 'required'],
            ['fio', 'string', 'max' => 512],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::className(), 'message' => Yii::t('app', 'This email address has already been taken')],
            ['password', 'string', 'min' => 8],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => Yii::t('app', 'email'),
            'password' => Yii::t('app', 'password'),
            'fio' => Yii::t('app', 'fio'),
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->scenario = User::SCENARIO_CREATE;
        $user->email = $this->email;
        $user->fio = $this->fio;
        $user->setPassword($this->password);
        $user->save();

        /** @var DbManager $authManager */
        $authManager = Yii::$app->authManager;
        /** @var Role $role */
        $role = $authManager->getRole($user->getRole());
        $authManager->assign($role, $user->id);
        $authManager->invalidateCache();

        return !$user->hasErrors() ? $user : null;
    }
}