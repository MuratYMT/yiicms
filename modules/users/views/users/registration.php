<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 30.06.2015
 * Time: 15:58
 */
use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\core\Settings;
use yiicms\modules\users\models\RegistrationForm;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;

/**  @var $this \yii\web\View */
/** @var $form  RegistrationForm */

Yii::$app->controller->layout = '@theme/views/layouts/login-layout';

?>

<div class="register-box">
    <div class="login-logo">
        <?= Settings::get('core.siteName') ?>
    </div>
    <!-- /.login-logo -->
    <div class="register-box-body">
        <p class="register-box-msg"><?= $this->title ?></p>

        <?php $activeForm = ActiveForm::begin(); ?>
        <?= $activeForm->field($form, 'email')
            ->label(false)
            ->textInput(['placeholder' => $form->getAttributeLabel('email')]); ?>

        <?= $activeForm->field($form, 'login')
            ->label(false)
            ->textInput(['placeholder' => $form->getAttributeLabel('login')]); ?>

        <?= $activeForm->field($form, 'password')
            ->label(false)
            ->passwordInput(['placeholder' => $form->getAttributeLabel('password')]); ?>

        <?= $activeForm->field($form, 'password2')
            ->label(false)
            ->passwordInput(['placeholder' => $form->getAttributeLabel('password2')]); ?>

        <?= $activeForm->field($form, 'timeZone')
            ->label(false)
            ->dropDownList(
                $form->availableTimeZones(),
                ['encode' => false, 'prompt' => $form->getAttributeLabel('timeZone')]
            ); ?>

        <?= $activeForm->field($form, 'phone')
            ->label(false)
            ->textInput(['placeholder' => $form->getAttributeLabel('phone')]); ?>

        <?= $activeForm->field($form, 'verifyCode')->label(false)->widget(
            Captcha::class,
            [
                'options' => ['placeholder' => $form->getAttributeLabel('verifyCode'), 'class' => 'form-control'],
                'captchaAction' => '/captcha',
            ]
        ) ?>

        <div class="row">
            <div class="col-xs-6">
                <?= $activeForm->field($form, 'ruleRead')->checkbox(); ?>
            </div>
            <!-- /.col -->
            <div class="col-xs-6">
                <?= SubmitButton::widget(['title' => \Yii::t('modules/users', 'Зарегестрироваться'), 'icon' => 'user-plus']); ?>
            </div>
            <!-- /.col -->
        </div>

        <?php ActiveForm::end(); ?>

        <?= Html::a(\Yii::t('modules/users', 'Уже зарегестрированы?'), ['/login']) ?>

    </div>
    <!-- /.register-box-body -->
</div>
