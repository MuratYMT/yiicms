<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.07.2015
 * Time: 15:17
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\core\Settings;

/**  @var $this \yii\web\View */
/** @var $form  \yiicms\components\core\LoginForm */

Yii::$app->controller->layout = '@theme/views/layouts/login-layout';

?>

<div class="login-box">
    <div class="login-logo">
        <?= Settings::get('core.siteName') ?>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg"><?= $this->title ?></p>

        <?php $activeForm = ActiveForm::begin(); ?>

        <?= $activeForm->field($form, 'email')
            ->label(false)
            ->textInput(['placeholder' => $form->getAttributeLabel('email')]); ?>

        <?= $activeForm->field($form, 'password')
            ->label(false)
            ->passwordInput(['placeholder' => $form->getAttributeLabel('password')]); ?>

        <div class="row">
            <div class="col-xs-8">
                <?= $activeForm->field($form, 'rememberMe')
                    ->checkbox(); ?>
            </div>
            <!-- /.col -->
            <div class="col-xs-4">
                <?= SubmitButton::widget(['title' => \Yii::t('modules/users', 'Войти'), 'icon' => 'sign-in']); ?>
            </div>
            <!-- /.col -->
        </div>
        <?php ActiveForm::end(); ?>

        <?= Html::a(\Yii::t('modules/users', 'Регистрация нового пользователя'), ['/registration']) ?>
        <br>
        <?= Html::a(\Yii::t('modules/users', 'Забыли пароль?'), ['/reset-password']) ?>

    </div>
    <!-- /.login-box-body -->
</div>

