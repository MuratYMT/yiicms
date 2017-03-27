<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 18.02.2017
 * Time: 21:43
 */

namespace yiicms\modules\admin\controllers;

use yii\filters\AccessControl;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\models\InstallForm;
use yiicms\models\core\Users;

class InstallController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function () {
                            if (!\Yii::$app->user->isGuest) {
                                return false;
                            }

                            return (int)Users::find()->count() === 0;
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $request = \Yii::$app->request;

        $form = new InstallForm();
        $form->scenario = InstallForm::SC_REGISTRATION;
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $form->load($request->post()) && $form->registration() !== false) {
            Alert::success(\Yii::t('modules/users', 'Регистрация завершилась успешно. Теперь вы можете войти'));
            return $this->redirect('/login');
        }

        \Yii::$app->view->title = \Yii::t('yiicms', 'Регистрация администатора');
        $this->layout = '@theme/views/layouts/login-layout';
        return $this->render('registration', ['form' => $form]);
    }
}