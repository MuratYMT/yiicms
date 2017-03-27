<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 16:52
 */

namespace yiicms\modules\users\controllers;

use yii\web\BadRequestHttpException;
use yiicms\components\core\LoginForm;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\modules\users\models\RegistrationForm;
use yiicms\modules\users\models\ResetPasswordForm;
use yiicms\modules\users\models\RestorePasswordForm;

/**
 * Class AuthController контроллер модуля пользователей
 * @package yiicms\modules\users\controllers
 */
class UsersController extends Controller
{
    const POPUP_LOGIN = 'login-form';

    /**
     * стандартный вход в систему
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionLogin()
    {
        $request = \Yii::$app->request;

        if (!\Yii::$app->user->isGuest) {
            Alert::success(\Yii::t('modules/users', 'Вы уже вошли'));
            return $this->goBack();
        }

        $form = new LoginForm();

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $form->load($request->post()) && $form->login()) {
            Alert::success(\Yii::t('modules/users', 'Вы успешно вошли'));
            return $this->goBack('/profile');
        }

        \Yii::$app->view->title = \Yii::t('modules/users', 'Вход');
        $form->password = '';
        return $this->render('login', ['form' => $form]);
    }

    /**
     * выход
     * @throws BadRequestHttpException
     */
    public function actionLogout()
    {
        if (\Yii::$app->request->isPost) {
            if (\Yii::$app->user->logout()) {
                Alert::success(\Yii::t('modules/users', 'Вы успешно вышли'));
                return $this->goHome();
            }
            Alert::error(\Yii::t('modules/users', 'Ошибка выхода'));
        }

        $this->view->title = \Yii::t('yiicms', 'Выход');
        return $this->render('logout');
    }

    /**
     * сброс пароля
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionResetPassword()
    {
        $request = \Yii::$app->request;
        $form = new ResetPasswordForm();
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $form->load($request->post()) && $form->sendEmail()) {
            Alert::success(
                \Yii::t(
                    'modules/users',
                    'На адрес {email} было отправлено письмо с инструкцией для восстановления пароля',
                    ['email' => $form->email]
                )
            );
            return $this->redirect(Url::to(['/restore-password']));
        }

        \Yii::$app->view->title = \Yii::t('modules/users', 'Сброс пароля');
        return $this->render('reset-password', ['form' => $form]);
    }

    /**
     * создание нового пароля
     * @param $token
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidParamException
     */
    public function actionRestorePassword($token = null)
    {
        $request = \Yii::$app->request;
        $form = new RestorePasswordForm(['token' => $token]);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $form->load($request->post()) && $form->resetPassword()) {
            Alert::success(\Yii::t('modules/users', 'Пароль успешно изменен. Теперь вы можете войти'));
            return $this->redirect([\Yii::$app->user->loginUrl]);
        }
        \Yii::$app->view->title = \Yii::t('modules/users', 'Восстановление пароля');
        return $this->render('restore-password', ['form' => $form]);
    }

    public function actionRegistration()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $request = \Yii::$app->request;

        $form = new RegistrationForm();
        $form->scenario = RegistrationForm::SC_FULL_REGISTRATION;
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $form->load($request->post())) {
            if ($form->registration() !== false) {
                Alert::success(\Yii::t('modules/users', 'Регистрация завершилась успешно. Теперь вы можете войти'));
                return $this->redirect('login');
            }
        }

        \Yii::$app->view->title = \Yii::t('modules/users', 'Регистрация');
        return $this->render('registration', ['form' => $form]);
    }
}
