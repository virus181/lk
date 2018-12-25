<?php
namespace app\controllers;

use app\components\UserException;
use app\models\Repository\Invoice;
use app\models\search\InvoiceSearch;
use app\models\User;
use Yii;
use yii\base\Exception;
use yii\web\Controller;

class AccountController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $registry = new InvoiceSearch();
        $dataProvider          = $registry->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $registry,
            'dataProvider' => $dataProvider,
            'context'      => []
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     */
    public function actionView($id)
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $shopInvoice = (new Invoice())->findOwn($id, $user);

        if (!$shopInvoice) {
            throw new UserException(403, 'Отказано в доступе');
        }

        return $this->render('view', [
            'invoice'  => $shopInvoice,
        ]);
    }
}