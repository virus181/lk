<?php
namespace app\api\controllers;

use app\api\base\BaseActiveController;
use app\components\DemoAutofiller;
use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;
use yii\rbac\Role;

class UserController extends BaseActiveController
{
    public $modelClass = 'app\models\User';
    public $searchModelClass = 'app\models\search\UserSearch';
    public $createScenario = User::SCENARIO_CREATE;

    public function init()
    {
        parent::init();

        if ($id = Yii::$app->request->get('id')) {
            /** @var User $user */
            $user = Yii::$app->user->identity;
            $model = User::findOne($id);

            if ($user->id === $model->id) {
                $this->updateScenario = User::SCENARIO_SELF_UPDATE;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
        ];
    }

    /**
     * @param \yii\base\Action $action
     * @param User $user
     * @return mixed
     */
    public function afterAction($action, $user)
    {
        if (!$user->hasErrors()) {
            if ($action->uniqueId == 'api/user/create' || $action->uniqueId == 'api/user/update') {
                /** @var DbManager $authManager */
                $authManager = Yii::$app->authManager;
                /** @var Role $role */
                $role = $authManager->getRole($user->getRole());
                $authManager->revokeAll($user->id);
                $authManager->assign($role, $user->id);
                $authManager->invalidateCache();
            }

            if ($action->uniqueId == 'api/user/create' && ArrayHelper::getValue(Yii::$app->params, 'demo.autofill')) {
                $demoAutofiller = new DemoAutofiller([
                    'user' => $user,
                ]);
                $demoAutofiller->run();
            }
        }


        return parent::afterAction($action, $user);
    }
}