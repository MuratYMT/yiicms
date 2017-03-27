<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 15.03.2017
 * Time: 11:48
 */
use yii\bootstrap\ActiveForm;
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
        <div class="row">
            <div class="col-xs-4">
                <?= SubmitButton::widget(['title' => \Yii::t('modules/users', 'Выйти'), 'icon' => 'sign-in']); ?>
            </div>
            <!-- /.col -->
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <!-- /.login-box-body -->
</div>