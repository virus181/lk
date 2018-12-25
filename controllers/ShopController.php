<?php

namespace app\controllers;

use app\models\Delivery;
use app\models\search\ShopSearch;
use app\models\Shop;
use app\models\ShopDelivery;
use app\models\ShopManager;
use app\models\ShopOption;
use app\models\ShopPhone;
use app\models\ShopTariff;
use app\models\ShopType;
use app\models\Tariff;
use app\models\traits\FindModelWithCheckAccessTrait;
use app\models\User;
use app\models\Warehouse;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

/**
 * ShopController implements the CRUD actions for Shop model.
 */
class ShopController extends Controller
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
        $this->modelName = Shop::className();
        parent::init();
    }

    /**
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ShopSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $exportProvider = $searchModel->export(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'    => $searchModel,
            'dataProvider'   => $dataProvider,
            'exportProvider' => $exportProvider
        ]);
    }

    /**
     * Creates a new Account model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     */
    public function actionCreate()
    {
        $shop = new Shop();
        $shop->scenario = Shop::SCENARIO_MANUAL;
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $warehouses = ArrayHelper::map(
            Warehouse::find()
                ->joinWith(['address'])
                ->andFilterWhere([
                    'warehouse.id' => $user->getAllowedWarehouseIds(),
                    'status'       => Warehouse::STATUS_ACTIVE
                ])
                ->asArray()
                ->all(),
            'id',
            'address.full_address'
        );

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (
                $shop->load(Yii::$app->request->post()) &&
                $shop->validate() &&
                $shop->save() &&
                (new Query())
                    ->createCommand()
                    ->insert('{{%user_shop}}', ['user_id' => Yii::$app->user->id, 'shop_id' => $shop->id])
                    ->execute()
            ) {

                if ($warehouseIds = Yii::$app->request->post('Shop')['warehouseIds']) {
                    $shop_warehouses = [];
                    foreach ((array)Yii::$app->request->post('Shop')['warehouseIds'] as $warehouseId) {
                        $shop_warehouses[] = [$shop->id, (int) $warehouseId];
                    }
                    (new Query())->createCommand()->batchInsert('{{%shop_warehouse}}', ['shop_id', 'warehouse_id'], $shop_warehouses)->execute();
                }

                $deliveryIds = array_unique((array)Yii::$app->request->post('Shop')['deliveries']);

                foreach ($deliveryIds as $delivery) {
                    $shopDelivery = new ShopDelivery();
                    $shopDelivery->shop_id = $shop->id;
                    $shopDelivery->delivery_id = (int)$delivery;
                    if (!$shopDelivery->save()) {
                        $transaction->rollBack();
                    }
                }

                foreach ($shop->types as $type => $value) {
                    if ($value) {
                        $shopType = new ShopType();
                        $shopType->shop_id = $shop->id;
                        $shopType->type = $type;
                        if (!$shopType->save()) {
                            $transaction->rollBack();
                        }
                    }
                }

                $transaction->commit();
                return $this->redirect(['index']);
            } else {
                $transaction->rollBack();
                return $this->render('create', [
                    'shop'               => $shop,
                    'warehouses'         => $warehouses,
                    'deliveries'         => Delivery::find()->asArray()->all(),
                    'tariffs'            => [],
                    'roundingItems'      => Shop::getRoundingItems(),
                    'deliveryTypes'      => Shop::getDeliveryTypeItems(),
                    'roundingItemValues' => Shop::getRoundingItemValues(),
                    'isActive'           => true,
                    'rights'             => [
                        'canBlockShop'         => Yii::$app->user->can('/shop/block'),
                        'canViewShopUser'      => Yii::$app->user->can('/shop/user-list'),
                        'canEnableFulfillment' => Yii::$app->user->can('/shop/enable-fulfillment'),
                        'canUpdateDelivery'    => Yii::$app->user->can('/shop/update-delivery-list'),
                        'canUpdateSkladId'     => Yii::$app->user->can('/shop/update-sklad-id'),
                        'canUpdateTariffs'     => (Yii::$app->user->can('/shop/update-tariff') && $shop->id),
                        'canUpdatePhones'      => (Yii::$app->user->can('/shop/update-phone') && $shop->id),
                    ],
                    'queryParams'        => [
                        'id' => null
                    ],
                ]);
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Updates an existing Account model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        /** @var Shop $shop */
        $shop = $this->findModel($id);
        $shop->getWarehouseIds();
        $shop->scenario = Shop::SCENARIO_MANUAL;
        $shop->setTypes($shop->getShopTypes());
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $warehouses = ArrayHelper::map(
            Warehouse::find()
                ->joinWith(['address'])
                ->andFilterWhere([
                    'warehouse.id' => $user->getAllowedWarehouseIds(),
                    'status'       => Warehouse::STATUS_ACTIVE
                ])
                ->asArray()
                ->all(),
            'id',
            'address.full_address'
        );

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($shop->load(Yii::$app->request->post()) && $shop->save()) {

                // Удалим все привязки к складам
                (new Query())->createCommand()->delete('{{%shop_warehouse}}', ['shop_id' => $shop->id])->execute();
                if ($warehouseIds = Yii::$app->request->post('Shop')['warehouseIds']) {
                    $shop_warehouses = [];
                    foreach ((array)Yii::$app->request->post('Shop')['warehouseIds'] as $warehouseId) {
                        $shop_warehouses[] = [$shop->id, (int) $warehouseId];
                    }
                    (new Query())->createCommand()->batchInsert('{{%shop_warehouse}}', ['shop_id', 'warehouse_id'], $shop_warehouses)->execute();
                }

                // Удалим все привязки к типам доставки
                ShopType::deleteAll(['shop_id' => $shop->id]);
                foreach ($shop->types as $type => $value) {
                    if ($value) {
                        $shopType = new ShopType();
                        $shopType->shop_id = $shop->id;
                        $shopType->type = $type;
                        $shopType->save();
                    }
                }

                if ($deliveryIds = Yii::$app->request->post('Shop')['deliveries']) {
                    if (!empty($deliveryIds)) {
                        // Удалим все привязки к службам доставки
                        ShopDelivery::deleteAll(['shop_id' => $shop->id]);
                        foreach ((array)Yii::$app->request->post('Shop')['deliveries'] as $deliveryId) {
                            $shopDelivery = new ShopDelivery();
                            $shopDelivery->shop_id = $shop->id;
                            $shopDelivery->delivery_id = $deliveryId;
                            if ($shopDelivery->validate()) {
                                $shopDelivery->save();
                            }
                        }
                    }
                }

                $transaction->commit();
                return $this->redirect(['shop/update', 'id' => $shop->id]);

            } else {
                $transaction->rollBack();
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->render('update', [
            'shop'               => $shop,
            'warehouses'         => $warehouses,
//            'managers'           => $managers,
            'isActive'           => $shop->status ? true : false,
            'tariffs'            => Tariff::find()->where(['shop_id' => $shop->id])->all(),
            'deliveries'         => Delivery::find()->asArray()->all(),
            'roundingItems'      => Shop::getRoundingItems(),
            'deliveryTypes'      => Shop::getDeliveryTypeItems(),
            'roundingItemValues' => Shop::getRoundingItemValues(),
            'queryParams'        => [
                'id' => $shop->id
            ],
            'rights'             => [
                'canBlockShop'         => Yii::$app->user->can('/shop/block'),
                'canViewShopUser'      => Yii::$app->user->can('/shop/user-list'),
                'canEnableFulfillment' => Yii::$app->user->can('/shop/enable-fulfillment'),
                'canUpdateDelivery'    => Yii::$app->user->can('/shop/update-delivery-list'),
                'canUpdateSkladId'     => Yii::$app->user->can('/shop/update-sklad-id'),
                'canUpdateTariffs'     => Yii::$app->user->can('/shop/update-tariff'),
                'canUpdatePhones'      => Yii::$app->user->can('/shop/update-phone'),
            ],
        ]);

    }

    /**
     * Updates an existing Account model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws Exception
     */
    public function actionView($id)
    {
        /** @var Shop $shop */
        $shop = $this->findModel($id);

        /** @var User $user */
        $user = Yii::$app->user->identity;

        return $this->renderAjax('view', [
            'shop'     => $shop,
            'isActive' => $shop->status ? true : false,
            'role'     => $user->getRole()
        ]);
    }

    /**
     * Deletes an existing Account model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        /** @var User $model */
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Блокировка магазина
     *
     * @param $id
     * @return \yii\web\Response
     */
    public function actionBlock($id)
    {
        /** @var Shop $shop */
        $shop = $this->findModel($id);
        $shop->status = Shop::STATUS_DELETED;
        if (!$shop->validate()) {
            foreach ($shop->getErrors() as $error) {
                Yii::$app->session->addFlash('danger', $error[0]);
            }
            return $this->redirect(['shop/update', 'id' => $id]);
        }
        $shop->save();
        Yii::$app->session->addFlash('success', Yii::t('app', 'Shop was blocked'));
        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return Shop
     */
    public function actionInfo($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        /** @var Shop $shop */
        $shop = $this->findModel($id);
        return $shop;
    }

    /**
     * @param int $id
     * @return string
     */
    public function actionUserList(int $id)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;
        $roles = $currentUser->getAllowedRoles();

        $shop = Shop::findOne($id);
        $users = $shop->users;
        foreach ($users as $user) {
            if (isset($roles[$user->getRole()]) && $user->status > 0) {
                $result[] = $user;
            }
        }

        return $this->renderAjax('_userList', [
            'users' => $result ?? []
        ]);
    }

    /**
     * @param $i
     * @return string
     */
    public function actionGetPhoneRow($i)
    {
        $phone = new ShopPhone();

        return $this->renderAjax('_phones', [
            'phone'          => $phone,
            'phoneProviders' => (new ShopPhone())->getPhoneProviders(),
            'i'              => ++$i,
        ]);
    }

    /**
     * Редактирование тарифа
     * @param int $id
     * @return string
     */
    public function actionTariffUpdate(int $id)
    {
        $shop = Shop::findOne(['id' => $id]);

        // Вытаскиваем активных пользователей (и привязанных к магазину и системных администраторвов)
        $managers = ArrayHelper::map(
            User::find()
                ->leftJoin(['user_shop'], 'user.id = user_shop.user_id')
                ->leftJoin(['auth_assignment'], 'user.id = auth_assignment.user_id')
                ->andFilterWhere([
                    'status' => User::STATUS_ACTIVE
                ])
                ->andWhere(['OR', ['user_shop.shop_id' => $id], ['auth_assignment.item_name' => User::ROLE_SYSTEM]])
                ->asArray()
                ->all(),
            'id',
            'fio'
        );

        // Сохраним информацию о менеджерах
        if (!empty(Yii::$app->request->post('Shop')['managerIds'])) {
            ShopManager::deleteAll(['shop_id' => $shop->id]);
            foreach (Yii::$app->request->post('Shop')['managerIds'] as $userId) {
                $shopManager = new ShopManager();
                $shopManager->shop_id = $shop->id;
                $shopManager->user_id = $userId;
                if ($shopManager->validate()) {
                    $shopManager->save();
                }
            }
        }

        if (!empty(Yii::$app->request->post('ShopTariff'))) {
            if (Yii::$app->request->post('ShopTariff')['id'] != '') {
                $shop->tariff = ShopTariff::findOne(Yii::$app->request->post('ShopTariff')['id']);
            } else {
                $shop->tariff = new ShopTariff();
                $shop->tariff->shop_id = $shop->id;
            }
            $shop->tariff->load(Yii::$app->request->post());
            if ($shop->tariff->validate()) {
                $shop->tariff->save();
                $shop->tariff_id = $shop->tariff->id;
                $shop->save();
                Yii::$app->session->addFlash('success', Yii::t('shop', 'Tariff was successfully changed'));
            } else {
                Yii::$app->session->addFlash('warning', Yii::t('shop', implode('<br />', $shop->tariff->errors)));
            }

            if (Yii::$app->request->post('ShopOption')['work_scheme_url']) {
                if (!$shop->option) {
                    $shop->option = new ShopOption();
                    $shop->option->shop_id = $shop->id;
                }

                $shop->option->work_scheme_url = Yii::$app->request->post('ShopOption')['work_scheme_url'];
                if ($shop->option->validate()) {
                    $shop->option->save();
                }
            }
        }

        return $this->renderAjax('_tariffForm', [
            'shop'       => $shop,
            'shopTariff' => $shop->tariff ?? new ShopTariff(),
            'shopOption' => $shop->option ?? new ShopOption(),
            'managers'   => $managers
        ]);
    }

    /**
     * Редактирование телефонии
     * @param int $id
     * @return string
     */
    public function actionPhoneUpdate(int $id)
    {
        $shop = Shop::findOne(['id' => $id]);
        if (!$shop->phones) {
            $shop->phones = [new ShopPhone()];
        }

        if (Yii::$app->request->post()) {

            ShopPhone::deleteAll(['shop_id' => $shop->id]);
            // Сохраним информацию о телефонах
            /** @var ShopPhone[] $phones */
            $phones = [];
            foreach (Yii::$app->request->post('ShopPhone', []) as $i => $phoneRequest) {
                $phones[$i] = new ShopPhone();
                $phones[$i]->shop_id = $shop->id;
            }
            ShopPhone::loadMultiple($phones, Yii::$app->request->post());
            $validatePhone = ShopPhone::validateMultiple($phones);
            if ($validatePhone) {
                foreach ($phones as $phone) {
                    $phone->save();
                }
                $shop->phones = $phones;
                Yii::$app->session->addFlash('success', Yii::t('shop', 'Phones was updated'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('shop', 'Shop number must be unique'));
            }

            if (!$shop->option) {
                $shop->option = new ShopOption();
                $shop->option->shop_id = $shop->id;
            }

            $shop->option->load(Yii::$app->request->post());
            if ($shop->option->validate()) {
                $shop->option->save();
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('shop', 'Queues wasnt saved'));
            }
        }

        return $this->renderAjax('_phonesForm', [
            'shop'           => $shop,
            'phoneProviders' => (new ShopPhone())->getPhoneProviders(),
            'shopOption'     => $shop->option ?? new ShopOption()
        ]);
    }

    /**
     * @return array
     */
    public function actionGetWorktimeByTariffCode()
    {
        $params = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        return (new ShopTariff())->getWorkTimeByTariffCode($params['tariffCode'] ? [$params['tariffCode']] : []);
    }
}
