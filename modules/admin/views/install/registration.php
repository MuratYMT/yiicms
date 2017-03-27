<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 18.02.2017
 * Time: 21:59
 */

use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\SubmitButton;
use yii\web\View;
use yiicms\models\InstallForm;

/**  @var $this View */
/** @var $form  InstallForm */

?>

<div class="login-box">
    <div class="login-logo">
        Yii2 CMS
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg"><?= $this->title ?></p>
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
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

        <div class="row">
            <!-- /.col -->
            <div class="col-xs-12">
                <?= SubmitButton::widget([
                    'title' => \Yii::t('modules/users', 'Зарегестрировать'),
                    'icon' => 'user-plus',
                    'style' => 'primary',
                ]); ?>
            </div>
            <!-- /.col -->
        </div>
        <?php ActiveForm::end(); ?>
        <?php Pjax::end() ?>
    </div>
    <!-- /.login-box-body -->
</div>