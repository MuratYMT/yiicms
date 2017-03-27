<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.07.2015
 * Time: 15:55
 */

namespace yiicms\modules\admin\controllers;

use yii\base\InvalidCallException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\modules\admin\models\roles\PermissionSearch;
use yiicms\modules\admin\models\roles\PermissionSearchRecursive;
use yiicms\modules\admin\models\roles\RoleEdit;
use yiicms\modules\admin\models\roles\RolesSearch;

class RolesController extends Controller
{
    /** @noinspection ClassMethodNameMatchesFieldNameInspection */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['AdminPermission'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add-child-role' => ['POST', 'GET'],
                    'add-role' => ['POST', 'GET'],
                    'del-child-role' => ['POST', 'GET'],
                    'edit-role' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'role-all-permission' => ['POST', 'GET'],
                    'role-permission' => ['POST', 'GET'],
                    'assign' => ['POST'],
                    'revoke' => ['POST'],
                    'del-role' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    /**
     * страница ролей
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $model = new RolesSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        $this->view->title = \Yii::t('yiicms', 'Существующие роли');

        return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    /**
     * страница разрешений назначенных непосредственно роли
     * @param string $roleName для какой роли
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRolePermission($roleName)
    {
        if (null === ($role = \Yii::$app->authManager->getRole($roleName))) {
            throw new NotFoundHttpException;
        }

        $model = new PermissionSearch();
        $dataProvider = $model->search($role, \Yii::$app->request->queryParams);

        $this->view->title = \Yii::t('yiicms', 'Разрешения назначенные роли "{role}"', ['role' => $role->name]);
        return $this->render('role-permission', ['dataProvider' => $dataProvider, 'model' => $model, 'roleName' => $role->name]);
    }

    public function actionAssign($roleName, $permissionName)
    {
        $auth = \Yii::$app->authManager;

        if (null === ($role = $auth->getRole($roleName)) || null === ($permission = $auth->getPermission($permissionName))) {
            throw new NotFoundHttpException;
        }

        if ($auth->hasChild($role, $permission)) {
            Alert::warning(\Yii::t('yiicms', 'Разрешение "{permission}" уже назначено', ['permission' => $permission->name]));
        } else {
            $auth->addChild($role, $permission);
            Alert::success(\Yii::t('yiicms', 'Разрешение "{permission}" назначено', ['permission' => $permission->name]));
        }

        return $this->actionRolePermission($roleName);
    }

    public function actionRevoke($roleName, $permissionName)
    {
        $auth = \Yii::$app->authManager;

        if (null === ($role = $auth->getRole($roleName)) || null === ($permission = $auth->getPermission($permissionName))) {
            throw new NotFoundHttpException;
        }

        if ($auth->hasChild($role, $permission)) {
            $auth->removeChild($role, $permission);
            Alert::success(\Yii::t('yiicms', 'Разрешение "{permission}" отозвано', ['permission' => $permission->name]));
        } else {
            Alert::warning(\Yii::t('yiicms', 'Разрешение "{permission}" не назначено', ['permission' => $permission->name]));
        }

        return $this->actionRolePermission($roleName);
    }

    public function actionRoleAllPermission($roleName)
    {
        if (null === ($role = \Yii::$app->authManager->getRole($roleName))) {
            throw new NotFoundHttpException;
        }

        $model = new PermissionSearchRecursive();
        $dataProvider = $model->search($role);

        $this->view->title = \Yii::t('yiicms', 'Все разрешения назначенные роли "{role}"', ['role' => $role->name]);
        return $this->render('role-all-permission', ['dataProvider' => $dataProvider]);
    }

    public function actionAddRole()
    {
        $request = \Yii::$app->request;

        $model = new RoleEdit();

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Роль "{role}" создана', ['role' => $model->name]));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Создание новой роли');
        return $this->render('edit-role', ['model' => $model]);
    }

    public function actionEditRole($roleName)
    {
        if (null === ($model = RoleEdit::findOne($roleName))) {
            throw new NotFoundHttpException;
        }

        $request = \Yii::$app->request;

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Роль "{role}" изменена', ['role' => $model->name]));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Редактирование роли "{role}"', ['role' => $model->name]);
        return $this->render('edit-role', ['model' => $model]);
    }

    public function actionDelRole($roleName)
    {
        $auth = \Yii::$app->authManager;

        if (null === ($role = $auth->getRole($roleName))) {
            throw new NotFoundHttpException;
        }

        if ($auth->remove($role)) {
            Alert::success(\Yii::t('yiicms', 'Роль "{role}" удалена', ['role' => $role->name]));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка удаления роли'));
        }
        return Url::goReturn();
    }

    public function actionAddChildRole($parentRole, $childRole = null)
    {
        $auth = \Yii::$app->authManager;
        if (null === ($parent = $auth->getRole($parentRole))) {
            throw new NotFoundHttpException;
        }

        if (\Yii::$app->request->isPost) {
            if (null === ($child = $auth->getRole($childRole))) {
                throw new NotFoundHttpException();
            }

            if ($auth->hasChild($parent, $child)) {
                Alert::warning(\Yii::t('yiicms', 'Эта роль уже назначена дочерней'));
            } else {
                try {
                    $auth->addChild($parent, $child);
                } catch (InvalidCallException $e) {
                    Alert::error(\Yii::t('yiicms', 'Выполнить невозможно. Обнаружена циклическая ссылка'));
                }
                Alert::success(\Yii::t('yiicms', 'Роль "{role}" добавлена как дочерняя', ['role' => $child->name]));
            }
            return Url::goReturn();
        }

        $searchModel = new RolesSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $this->view->title = \Yii::t('yiicms', 'Добавление дочерних ролей для "{role}"', ['role' => $parent->name]);
        return $this->render('add-childs-roles', ['model' => $searchModel, 'dataProvider' => $dataProvider, 'parentRoleName' => $parent->name]);
    }

    public function actionDelChildRole($parentRole, $childRole = null)
    {
        $auth = \Yii::$app->authManager;
        if (null === ($parent = $auth->getRole($parentRole))) {
            throw new NotFoundHttpException;
        }

        if (\Yii::$app->request->isPost) {
            if (null === ($child = $auth->getRole($childRole))) {
                throw new NotFoundHttpException();
            }

            if ($auth->hasChild($parent, $child)) {
                $auth->removeChild($parent, $child);
                Alert::success(\Yii::t('yiicms', 'Роль "{role}" удалена из дочерних', ['role' => $child->name]));
            } else {
                Alert::warning(\Yii::t('yiicms', 'Эта роль не является дочерней'));
            }
            return Url::goReturn();
        }

        $searchModel = new RolesSearch();
        $dataProvider = $searchModel->searchChilds($parent);
        $this->view->title = \Yii::t('yiicms', 'Удаление дочерних ролей из "{role}"', ['role' => $parentRole]);
        return $this->render('del-child-role', ['model' => $searchModel, 'dataProvider' => $dataProvider, 'parentRoleName' => $parent->name]);
    }
}
