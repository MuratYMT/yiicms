<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 02.07.2015
 * Time: 10:25
 */

namespace yiicms\modules\users\controllers;

use yii\filters\VerbFilter;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\models\core\Users;
use yiicms\modules\users\models\ChangePasswordForm;
use yiicms\modules\users\models\PhotoSetForm;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;

class ProfileController extends Controller
{
    public $userId;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function () {
                            return \Yii::$app->user->can('ProfileEdit', ['profileUserId' => $this->userId]);
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'change-password' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'photo' => ['POST', 'GET'],
                    'photo-del' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    public function beforeAction($action)
    {
        $this->userId = 0;
        if (!empty(\Yii::$app->request->get('userId'))) {
            $this->userId = \Yii::$app->request->get('userId');
        } elseif (!\Yii::$app->user->isGuest) {
            $this->userId = \Yii::$app->user->id;
        }
        return parent::beforeAction($action);
    }

    /**
     * смена пароля
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionChangePassword()
    {
        $request = \Yii::$app->request;

        $form = new ChangePasswordForm(['userId' => $this->userId]);
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $form->load($request->post()) && $form->changePassword()) {
            Alert::success(\Yii::t('modules/users', 'Пароль изменен'));
        }

        $form->oldPassword = '';
        $form->password = '';
        $form->password2 = '';

        $this->view->title = \Yii::t('modules/users', 'Смена пароля');
        return $this->render('change-password', ['form' => $form]);
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     */
    public function actionPhoto()
    {
        $request = \Yii::$app->request;

        $form = new PhotoSetForm(['userId' => $this->userId]);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $form->load($request->post()) && $form->setPhoto()) {
            Alert::success(\Yii::t('modules/users', 'Фотография установлена'));
        }
        $this->view->title = \Yii::t('modules/users', 'Фотография');
        return $this->render('photo-set', ['form' => $form]);
    }

    public function actionPhotoDel()
    {
        $form = new PhotoSetForm(['userId' => $this->userId]);
        if ($form->delPhoto()) {
            Alert::success(\Yii::t('modules/users', 'Фотография удалена'));
        }
        return Url::goReturn();
    }

    /**
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $model = Users::findOne($this->userId);
        $model->scenario = Users::SC_PROFILE_EDIT;

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('modules/users', 'Данные профиля обновлены'));
        }

        $this->view->title = \Yii::t('modules/users', 'Профиль');
        return $this->render('index', ['model' => $model]);
    }
}
