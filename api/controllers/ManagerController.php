<?php
namespace app\api\controllers;

use app\api\base\BaseActiveController;
use app\api\Module;
use app\api\view\User\Lists;
use app\models\User;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use yii\web\Response;

class ManagerController extends BaseActiveController
{
    public $modelClass = 'app\models\User';

    /**
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        Yii::$app->params['environment'] = 'api';
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $params = Yii::$app->request->get();

        $query = User::find()
            ->leftJoin('auth_assignment', 'user.id = auth_assignment.user_id')
            ->where(['status' => User::STATUS_ACTIVE])
            ->andWhere([
            'IN', 'auth_assignment.item_name', array_keys($user->getAllowedRoles())
        ]);

        if (!empty($params['ids'])) {
            $userIds = explode(',', $params['ids']);
            $query->andWhere(['IN', 'user.id', $userIds]);
        }

        $users = $query->asArray()->all();

        Yii::$app->response->format = Response::FORMAT_JSON;

        return new ArrayDataProvider([
            'allModels' => (new Lists())->setUsers($users)->build(),
            'pagination' => [
                'class' => Pagination::className(),
                'pageSizeLimit' => [1, count($users)],
                'pageSize' => count($users),
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        $verbs = parent::verbs();
        $verbs['list'] = ['GET'];
        return $verbs;
    }
}