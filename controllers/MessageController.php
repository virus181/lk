<?php

namespace app\controllers;

use app\models\Message;
use app\models\Product;
use app\models\search\ProductSearch;
use app\models\Shop;
use app\models\traits\FindModelWithCheckAccessTrait;
use app\models\Upload;
use app\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class MessageController extends Controller
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
     * Creates a new Message model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Message();
        $user = User::findOne(Yii::$app->user->identity->getId());
        $model->fio = $user->fio;
        $model->phone = $user->fio;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->imageFile = UploadedFile::getInstance($model, 'file');
            $path = 'uploads/';
            if ($model->imageFile && $model->upload($path)) {
                $model->file = $path . $model->imageFile->baseName . '.' . $model->imageFile->extension;
                $model->imageFile = null;
            }

            if ($model->validate() && $model->save()) {
                return $this->renderAjax('/main/success', [
                    'title' => Yii::t('app', 'Your message was sended')
                ]);
            }

        } else {
            return $this->renderAjax('create', [
                'model' => $model
            ]);
        }
    }

}
