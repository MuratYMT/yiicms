<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 17:13
 */

use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\core\Settings;
use yiicms\modules\users\models\ResetPasswordForm;
use yii\bootstrap\ActiveForm;

/**  @var $this \yii\web\View */
/** @var $form  ResetPasswordForm */

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
        <?= $activeForm->field($form, 'email')->label(false)->textInput(['placeholder' => $form->getAttributeLabel('email')]); ?>
        <div class="row">
            <div class="col-xs-12">
                <?= SubmitButton::widget(['title' => \Yii::t('modules/users', 'Сбросить пароль'), 'icon' => 'exchange']); ?>
            </div>
            <!-- /.col -->
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <!-- /.login-box-body -->
</div>
