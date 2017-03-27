<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 06.07.2015
 * Time: 9:07
 */

namespace yiicms\modules\admin\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\models\core\Users;
use yiicms\modules\admin\models\users\ChangePasswordForm;
use yiicms\modules\admin\models\users\PermissionSearch;
use yiicms\modules\admin\models\users\RolesSearch;
use yiicms\modules\admin\models\users\UsersSearch;

/**
 * Class AuthController контроллер управления пользователями
 * @package yiicms\modules\admin\controllers
 */
class UsersController extends Controller
{
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
                    'change-password' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'permission' => ['POST', 'GET'],
                    'roles' => ['POST', 'GET'],
                    'role-assign' => ['POST'],
                    'role-revoke' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $model = new UsersSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        $this->view->title = \Yii::t('yiicms', 'Пользователи');

        return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    /**
     * смена пароля
     * @param $userId
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionChangePassword($userId)
    {
        $request = \Yii::$app->request;

        $model = new ChangePasswordForm();

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->changePassword($userId)) {
            Alert::success(\Yii::t('yiicms', 'Пароль изменен'));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Смена пароля');
        return $this->render('change-password', ['model' => $model]);
    }

    public function actionPermission($userId)
    {
        $user = Users::findOne($userId);
        if ($user === null) {
            throw  new NotFoundHttpException;
        }

        $model = new PermissionSearch(['user' => $user]);
        $dataProvider = $model->search(\Yii::$app->request->queryParams);

        $this->view->title = \Yii::t(
            'yiicms',
            'Разрешения назначенные пользователю "{user}"',
            ['user' => $model->user->login]
        );

        return $this->render('user-permission', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    public function actionRoles($userId)
    {
        $user = Users::findOne($userId);
        if ($user === null) {
            throw  new NotFoundHttpException;
        }

        $modelSearch = new RolesSearch(['user' => $user]);
        $dataProvider = $modelSearch->search(\Yii::$app->request->queryParams);
        $this->view->title = \Yii::t('yiicms', 'Роли назначенные пользователю "{user}"', ['user' => $user->login]);
        return $this->render('user-roles', ['dataProvider' => $dataProvider, 'model' => $modelSearch, 'userId' => $userId]);
    }

    public function actionRoleAssign($userId, $roleName)
    {
        $auth = \Yii::$app->authManager;
        list ($user, $role) = self::assignRevokeCheck($userId, $roleName);

        if (null === $auth->getAssignment($role->name, $user->userId)) {
            $auth->assign($role, $user->userId);
            Alert::success(\Yii::t('yiicms', 'Роль "{role}" назначена пользователю', ['role' => $role->name]));
        } else {
            Alert::warning(\Yii::t('yiicms', 'Роль "{role}" уже назначена пользователю', ['role' => $role->name]));
        }
        return $this->actionRoles($userId);
    }

    public function actionRoleRevoke($userId, $roleName)
    {
        $auth = \Yii::$app->authManager;
        list ($user, $role) = self::assignRevokeCheck($userId, $roleName);
        if (null === $auth->getAssignment($role->name, $user->userId)) {
            Alert::warning(\Yii::t('yiicms', 'Роль "{role}" не назначена пользователю', ['role' => $role->name]));
        } else {
            $auth->revoke($role, $user->userId);
            Alert::success(\Yii::t('yiicms', 'Роль "{role}" отозвана у пользователя', ['role' => $role->name]));
        }
        return $this->actionRoles($userId);
    }

    /**
     * @param int $userId
     * @param string $roleName
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    private static function assignRevokeCheck($userId, $roleName)
    {
        if (null === ($user = Users::findById($userId)) || null === ($role = \Yii::$app->authManager->getRole($roleName))) {
            throw  new NotFoundHttpException;
        }

        return [$user, $role];
    }
}
