<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12.01.2016
 * Time: 15:02
 */

namespace yiicms\modules\admin\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yiicms\components\core\Url;
use yiicms\components\YiiCms;
use yiicms\modules\admin\models\menus\MenuSearch;
use yiicms\modules\admin\models\menus\MenusVisibleForPathInfoSearch;
use yiicms\modules\admin\models\menus\MenusVisibleForRoleSearch;
use yiicms\components\core\TraitLangCheck;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\models\core\Menus;
use yiicms\models\core\MenusVisibleForPathInfo;
use yiicms\models\core\Settings;

class MenusController extends Controller
{
    use TraitLangCheck;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST', 'GET'],
                    'edit' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'path-info-visible' => ['POST', 'GET'],
                    'path-info-visible-add' => ['POST', 'GET'],
                    'path-info-visible-edit' => ['POST', 'GET'],
                    'role-visible' => ['POST', 'GET'],
                    'del-menu' => ['POST'],
                    'role-visible-grant' => ['POST'],
                    'role-visible-revoke' => ['POST'],
                    'children-visible-as-this' => ['POST'],
                    'path-info-visible-del' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    /**
     * страница меню
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $model = new MenuSearch();

        $dataProvider = $model->search($request->queryParams);
        $this->view->title = \Yii::t('yiicms', 'Структура меню сайта');
        return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    public function actionAdd($parentId = 0)
    {
        $request = \Yii::$app->request;

        $model = new Menus;
        $model->parentId = $parentId;
        $model->scenario = Menus::SC_EDIT;

        $menuService = YiiCms::$app->menuService;

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $menuService->save($model)) {
            Alert::success(\Yii::t('yiicms', 'Пункт меню создан'));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Создать пункт меню');
        return $this->render('menu-edit', ['model' => $model]);
    }

    public function actionEdit($menuId)
    {
        $request = \Yii::$app->request;

        $model = self::findMenu($menuId);
        $model->scenario = Menus::SC_EDIT;

        $menuService = YiiCms::$app->menuService;

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $menuService->save($model)) {
            Alert::success(\Yii::t('yiicms', 'Пункт меню "{menu}" отредактирован', ['menu' => $model->title]));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Изменить пункт меню "{menu}"', ['menu' => $model->title]);
        return $this->render('menu-edit', ['model' => $model]);
    }

    public function actionDelMenu($menuId, $removeChild = 0)
    {
        $menu = self::findMenu($menuId);

        if (YiiCms::$app->menuService->delete($menu, (int)$removeChild === 1)) {
            Alert::success(\Yii::t('yiicms', 'Пункт меню "{menu}" удален', ['menu' => $menu->title]));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка удаления пункта меню'));
        }
        return Url::goReturn();
    }

    public function actionRoleVisible($menuId)
    {
        $menu = self::findMenu($menuId);

        $model = new MenusVisibleForRoleSearch();
        $dataProvider = $model->search($menu, \Yii::$app->request->queryParams);

        $this->view->title = \Yii::t('yiicms', 'Роли для которых виден пункт меню "{menu}"', ['menu' => $menu->title]);
        return $this->render(
            'role-permission',
            ['dataProvider' => $dataProvider, 'menuId' => $menu->menuId, 'model' => $model]
        );
    }

    public function actionRoleVisibleGrant($menuId, $roleName, $recursive = false)
    {
        $menu = self::findMenuAndCheckRole($menuId, $roleName);
        YiiCms::$app->menuService->grant($menu, $roleName, $recursive);
        Alert::success(
            \Yii::t('yiicms', 'Видимость пункта меню для роли "{role}" предоставлена', ['role' => $roleName])
        );
        return Url::goReturn();
    }

    public function actionRoleVisibleRevoke($menuId, $roleName, $recursive = false)
    {
        $menu = self::findMenuAndCheckRole($menuId, $roleName);
        YiiCms::$app->menuService->revoke($menu, $roleName, $recursive);

        Alert::success(\Yii::t('yiicms', 'Видимость пункта меню для роли "{role}" отменена', ['role' => $roleName]));
        return Url::goReturn();
    }

    public function actionChildrenVisibleAsThis($menuId)
    {
        $model = self::findMenu($menuId);
        if (YiiCms::$app->menuService->replaceChildrenVisibleForRole($model)) {
            Alert::success(\Yii::t('yiicms', 'Видимость для ролей дочерних пунктов меню установлена'));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка изменения видимости для ролей'));
        }
        return Url::goReturn();
    }

    public function actionPathInfoVisible($menuId)
    {
        $menu = self::findMenu($menuId);

        $model = new MenusVisibleForPathInfoSearch();
        $dataProvider = $model->search($menuId);

        $this->view->title = \Yii::t(
            'yiicms',
            'Правила видимости пункта меню "{menu}" на страницах сайта',
            ['menu' => $menu->title]
        );
        return $this->render(
            'pathinfo-permission',
            ['dataProvider' => $dataProvider, 'menuId' => $menu->menuId, 'model' => $model]
        );
    }

    public function actionPathInfoVisibleAdd($menuId)
    {
        $request = \Yii::$app->request;

        $menu = self::findMenu($menuId);
        $model = new MenusVisibleForPathInfo(['menuId' => $menu->menuId]);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Правило добавлено'));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t(
            'yiicms',
            'Добавить правило видимости пункта меню "{menu}" на страницах сайта',
            ['menu' => $menu->title]
        );
        return $this->render('pathinfo-visible-edit', ['model' => $model]);
    }

    public function actionPathInfoVisibleEdit($permId)
    {
        if (null === ($model = MenusVisibleForPathInfo::findOne($permId))) {
            throw new NotFoundHttpException;
        }

        $request = \Yii::$app->request;
        $menu = self::findMenu($model->menuId);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Правило изменено'));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t(
            'yiicms',
            'Изменить правило видимости пункта меню "{menu}" на страницах сайта',
            ['menu' => $menu->title]
        );
        return $this->render('pathinfo-visible-edit', ['model' => $model]);
    }

    public function actionPathInfoVisibleDel($permId)
    {
        if (null === ($model = MenusVisibleForPathInfo::findOne($permId))) {
            throw new NotFoundHttpException;
        }

        $menu = self::findMenu($model->menuId);

        if ($model->delete()) {
            Alert::success(\Yii::t(
                'yiicms',
                'Правило видимости для пункта меню "{menu}" удалено',
                ['menu' => $menu->title]
            ));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка удаления правила'));
        }
        return Url::goReturn();
    }

    /**
     * @param $menuId
     * @param $roleName
     * @return Menus
     * @throws NotFoundHttpException
     */
    private static function findMenuAndCheckRole($menuId, $roleName)
    {
        if ($roleName !== Settings::get('users.defaultGuestRole')
            && null === \Yii::$app->authManager->getRole($roleName)
        ) {
            throw new NotFoundHttpException;
        }

        return self::findMenu($menuId);
    }

    /**
     * ищет пункт меню. если не находит выбрасывает ошибку
     * @param int $menuId искомый пункт меню
     * @return Menus
     * @throws NotFoundHttpException
     */
    private static function findMenu($menuId)
    {
        if (null === ($model = Menus::findOne(['menuId' => $menuId]))) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
}
