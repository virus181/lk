<?php

namespace app\controllers;

use app\models\Address;
use app\models\search\WarehouseSearch;
use app\models\Warehouse;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * WarehouseController implements the CRUD actions for Warehouse model.
 */
class WarehouseController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Warehouse models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $exportProvider = $searchModel->export(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'exportProvider' => $exportProvider,
        ]);
    }

    /**
     * Creates a new Warehouse model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     */
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        $warehouse = new Warehouse();
        $address = new Address();
        $address->scenario = Address::SCENARIO_ADDRESS_FULL;
        $warehouse->address = $address;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($warehouse->address->load($post) &&
                $warehouse->load($post) &&
                $warehouse->save() &&
                (new Query())->createCommand()->insert(
                    '{{%user_warehouse}}',
                    ['user_id' => Yii::$app->user->id, 'warehouse_id' => $warehouse->id]
                )->execute()
            ) {
                $transaction->commit();
                Yii::$app->session->addFlash('success', Yii::t('app', 'Warehouse {name} was created', [
                    'name' => $warehouse->name
                ]));
                return $this->redirect(['index']);
            } else {
                $transaction->rollBack();
                if ($warehouse->address->load($post) && $warehouse->load($post)) {
                    $warehouse->address->validate();
                    $warehouse->validate();
                }
                return $this->renderAjax('create', [
                    'warehouse' => $warehouse,
                ]);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Updates an existing Warehouse model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $post = Yii::$app->request->post();
        $warehouse = $this->findModel($id);
        $warehouse->address->scenario = Address::SCENARIO_ADDRESS_FULL;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (
                $warehouse->address->load($post) &&
                $warehouse->load($post) &&
                $warehouse->save()
            ) {
                $transaction->commit();
                return $this->redirect(['index']);
            } else {
                $transaction->rollBack();
                return $this->render('update', [
                    'warehouse' => $warehouse,
                ]);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Updates an existing Warehouse model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws Exception
     */
    public function actionView($id)
    {
        $warehouse = $this->findModel($id);

        return $this->renderAjax('view', [
            'warehouse' => $warehouse,
        ]);
    }

    /**
     * Finds the Warehouse model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Warehouse the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Warehouse::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Deletes an existing Warehouse model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
}
