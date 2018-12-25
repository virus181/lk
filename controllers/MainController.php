<?php

namespace app\controllers;

use app\components\DemoAutofiller;
use app\components\Stat;
use app\models\forms\ForgotPassword;
use app\models\forms\LoginForm;
use app\models\forms\ResetPasswordForm;
use app\models\forms\SignupForm;
use app\queue\DemoJob;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class MainController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'login', 'signup', 'forgot-password', 'reset-password'],
                'denyCallback' => function () {
                    Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl())->send();
                },
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login', 'signup', 'forgot-password', 'reset-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        if (Yii::$app->user->isGuest) {
            $this->layout = 'auth';
        }
        return [];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return string
     */
    public function actionOffer()
    {
        return $this->renderAjax('offer');
    }

    /**
     * Страница о требованиях упаковки
     *
     * @return string
     */
    public function actionPackage(): string
    {
        return $this->render('package');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = 'auth';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        if (!ArrayHelper::getValue(Yii::$app->params, 'demo')) {
            $this->goHome();
        }
        $this->layout = 'auth';

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && ($user = $model->signup()) && Yii::$app->getUser()->login($user)) {
            return $this->goHome();
        }
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionForgotPassword()
    {
        $this->layout = 'auth';

        $model = new ForgotPassword();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }
        return $this->render('forgotPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        $this->layout = 'auth';

        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');
            return $this->goHome();
        }
        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionDoc()
    {
        $this->layout = 'doc';
        return $this->render('doc');
    }

    public function afterAction($action, $result)
    {
        if ($action->uniqueId == 'main/signup' && Yii::$app->user->id && ArrayHelper::getValue(Yii::$app->params, 'demo.autofill')) {
            $demoAutofiller = new DemoAutofiller([
                'user' => Yii::$app->user->identity,
            ]);
            $demoAutofiller->run();
        }

        return parent::afterAction($action, $result);

//        if ($action->uniqueId == 'main/signup'
//            && Yii::$app->user->id && ArrayHelper::getValue(Yii::$app->params, 'demo.autofill')
//        ) {
//            Yii::$app->queue->push(new DemoJob([
//                'userId' => Yii::$app->user->id
//            ]));
//            Yii::$app->session->setFlash('success', 'Вы успешно зарегестировались в Demo кабинете Fastery.
//            Demo данные будут загруженны в Вашу учетную запись в ближайшие пару минут.
//            Будьте внимательны, демо кабинет будет автоматически очищен через 30 дней.
//            После чего вы сможете заново зарегистирироваться в Demo кабинете');
//        }
//
//        return parent::afterAction($action, $result);
    }

    /**
     * Обработчик ошибок
     *
     * @return string
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            $user = Yii::$app->user->getIdentity();

            if (Yii::$app->response->getStatusCode() >= 500) {
                $errorCode = md5($exception->getFile() . $exception->getLine() . $exception->getMessage());
                Yii::$app->slack->send('UserId #' . $user->getId() . ' ' . $exception->getMessage() . ' ( ' . Yii::$app->response->getStatusCode() . ' )', ':thumbs_up:', [
                    [
                        'fallback' => 'Ошибка в ЛК',
                        'color' => Yii::$app->slack->getLevelColor(Logger::LEVEL_ERROR),
                        'fields' => [
                            [
                                'title' => 'File',
                                'value' => $exception->getFile() . ':' . $exception->getLine(),
                                'short' => false,
                            ],
                            [
                                'title' => 'Error',
                                'value' => $errorCode,
                                'short' => false,
                            ],
                            [
                                'title' => 'Trace',
                                'value' => $exception->getTraceAsString(),
                                'short' => false,
                            ]
                        ],
                    ],
                ]);
            }

            // Отправим ошибку в сбор статистики
            /** @var Stat $stat */
            $stat = Yii::$app->get('stat');
            $stat->sendErrorEvent([
                'code' => Yii::$app->response->getStatusCode(),
                'message' => $exception->getMessage(),
                'userId' => $user->getId(),
                'type' => 'lk'
            ]);

            return $this->render('error', [
                'exception' => $exception,
                'name' => Yii::$app->response->getStatusCode() < 500
                    ? $exception->getMessage()
                    : 'Упс, что-то пошло не так!',
                'code' => $errorCode ?? null,
                'statusCode' => Yii::$app->response->getStatusCode()
            ]);
        }
    }
}
