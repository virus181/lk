<?php

namespace app\controllers;

use app\models\Manager;
use app\models\search\UserSearch;
use app\models\Shop;
use app\models\traits\FindModelWithCheckAccessTrait;
use app\models\User;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\rbac\DbManager;
use yii\rbac\Role;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    use FindModelWithCheckAccessTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function init()
    {
        $this->modelName = User::className();
        parent::init();
    }

    /**
     * Lists all User models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel    = new UserSearch();
        $dataProvider   = $searchModel->search(Yii::$app->request->queryParams);
        $exportProvider = $searchModel->export(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'    => $searchModel,
            'dataProvider'   => $dataProvider,
            'exportProvider' => $exportProvider,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionCreate()
    {
        $emailSended     = null;
        $emailSended     = null;
        $model           = new User();
        $model->scenario = User::SCENARIO_CREATE;

        /** @var User $user */
        $user      = Yii::$app->user->identity;
        $available = [
            'canChangeOperatorInfo' => Yii::$app->user->can('/user/operator-update')
        ];

        /** @var DbManager $authManager */
        $authManager = Yii::$app->authManager;

        $transaction = Yii::$app->db->beginTransaction();

        /** @var Role $role */
        if (
            $model->load(Yii::$app->request->post()) &&
            $model->save() &&
            ($role = $authManager->getRole($model->getRole())) &&
            ($emailSended = $model->sendNewUserEmail()) &&
            $authManager->assign($role, $model->id)
        ) {
            $authManager->invalidateCache();
            if ($shopIds = Yii::$app->request->post('User')['shopIds']) {
                $user_shops      = [];
                $user_warehouses = [];
                foreach ((array)Yii::$app->request->post('User')['shopIds'] as $shopId) {
                    $shop              = Shop::findOne($shopId);
                    $user_shops[]      = [$model->id, $shop->id];
                    $user_warehouses[] = [$model->id, $shop->default_warehouse_id];
                }
                (new Query())->createCommand()->batchInsert('{{%user_shop}}', ['user_id', 'shop_id'], $user_shops)->execute();
                (new Query())->createCommand()->batchInsert('{{%user_warehouse}}', ['user_id', 'warehouse_id'], $user_warehouses)->execute();
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', Yii::t('app', 'User has been created'));

            return $this->redirect(['index']);
        } else {
            $transaction->rollBack();

            if ($emailSended === false) {
                Yii::$app->session->setFlash('danger', Yii::t('app', 'Accesses was not sended. Please try agail later'));
            }

            if (Yii::$app->request->post() && !$model->hasErrors()) {
                Yii::$app->session->setFlash('danger', Yii::t('app', 'There was an error adding the shop for user'));
            }

            return $this->render('create', [
                'model'     => $model,
                'shops'     => $user->getAllowedShops(),
                'available' => $available,
                'manager'   => new Manager()
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        /** @var User $model */
        $model = $this->findModel($id);

        /** @var User $user */
        $user      = Yii::$app->user->identity;
        $available = [
            'canChangeOperatorInfo' => Yii::$app->user->can('/user/operator-update')
        ];

        if ($user->id === $model->id) {
            $model->setScenario(User::SCENARIO_SELF_UPDATE);
        }

        /** @var DbManager $authManager */
        $authManager = Yii::$app->authManager;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (
                $model->load(Yii::$app->request->post()) &&
                $model->save()
            ) {
                if ($model->scenario === User::SCENARIO_DEFAULT) {
                    /** @var Role $role */
                    $role = $authManager->getRole($model->getRole());
                    $authManager->revokeAll($model->id);
                    $authManager->assign($role, $model->id);

                    $authManager->invalidateCache();

                    (new Query())->createCommand()->delete('{{%user_shop}}', ['user_id' => $model->id])->execute();
                    (new Query())->createCommand()->delete('{{%user_warehouse}}', ['user_id' => $model->id])->execute();

                    if ($shopIds = Yii::$app->request->post('User')['shopIds']) {
                        $user_shops      = [];
                        $user_warehouses = [];
                        foreach ((array)Yii::$app->request->post('User')['shopIds'] as $shopId) {
                            $shop              = Shop::findOne($shopId);
                            $user_shops[]      = [$model->id, $shopId];
                            $user_warehouses[] = [$model->id, $shop->default_warehouse_id];
                        }
                        (new Query())->createCommand()->batchInsert('{{%user_shop}}', ['user_id', 'shop_id'], $user_shops)->execute();
                        (new Query())->createCommand()->batchInsert('{{%user_warehouse}}', ['user_id', 'warehouse_id'], $user_warehouses)->execute();
                    }
                }
                $transaction->commit();
                $this->closeModal();
                return $this->redirect(['index']);
            } else {
                $transaction->rollBack();
                return $this->render('update', [
                    'model'     => $model,
                    'shops'     => $user->getAllowedShops(),
                    'available' => $available,
                    'manager'   => new Manager()
                ]);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            return $this->renderAjax('update', [
                'model'     => $model,
                'shops'     => $user->getAllowedShops(),
                'available' => $available,
                'manager'   => new Manager()
            ]);
        }
    }

    /**
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        /** @var User $model */
        $model = $this->findModel($id);

        /** @var User $user */
        $user = Yii::$app->user->identity;

        return $this->renderAjax('view', [
            'model' => $model,
            'shops' => $user->getAllowedShops(),
        ]);

    }

    protected function closeModal()
    {
        echo '<script>
                    var id = $(\'.grid-view\').parents(\'[data-pjax-container]\').attr(\'id\');
                    $(\'.modal:visible\').modal(\'hide\');
                    if (id !== undefined) {
                        $.pjax.reload({container:\'#\'+id});
                    }
              </script>';
    }

    public function actionResetAccessToken($id)
    {
        /** @var User $user */
        $user = $this->findModel($id);
        $user->resetAccessToken();
        sleep(1);
        if ($user->save()) {
            return $user->access_token;
        } else {
            Yii::$app->response->setStatusCode('422');
            return Yii::t('app', 'Reset access-token failed');
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }
}
