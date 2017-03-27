<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 17:13
 */

use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\core\Settings;
use yiicms\modules\users\models\RestorePasswordForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**  @var $this \yii\web\View */
/** @var $form  RestorePasswordForm */

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
        <?= $activeForm->field($form, 'token')->label(false)->textInput(['placeholder' => $form->getAttributeLabel('token')]); ?>
        <?= $activeForm->field($form, 'password')->label(false)->passwordInput(['placeholder' => $form->getAttributeLabel('password')]); ?>
        <?= $activeForm->field($form, 'password2')->label(false)->passwordInput(['placeholder' => $form->getAttributeLabel('password2')]); ?>
        <div class="row">
            <div class="col-xs-12">
                <?= SubmitButton::widget(['title' => \Yii::t('modules/users', 'Изменить пароль'), 'icon' => 'exchange']); ?>
            </div>
            <!-- /.col -->
        </div>
        <?php ActiveForm::end(); ?>
        <?= Html::a(\Yii::t('modules/users', 'Повторный сброс пароля'), ['/reset-password']) ?>
    </div>
    <!-- /.login-box-body -->
</div>