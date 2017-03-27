<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.12.2016
 * Time: 17:04
 */
use yiicms\components\core\widgets\SubmitButton;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yiicms\modules\users\controllers\UsersController;

/**
 * @var $this View
 * @var $model \yiicms\components\core\LoginForm
 */
?>

<div id="<?= UsersController::POPUP_LOGIN; ?>" class="modal-dialog">
    <?php $form = ActiveForm::begin(['options' => ['data-ajaxform' => true]]); ?>
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h3 class="modal-title"><?= $this->title; ?></h3>
        </div>
        <div class="modal-body">
            <?= $form->field($model, 'email')->textInput(); ?>
            <?= $form->field($model, 'password')->passwordInput(); ?>
            <?= $form->field($model, 'rememberMe')->checkbox(); ?>
            <div><?= Html::a(\Yii::t('modules/users', 'Забыли пароль?'), ['/reset-password']) ?></div>
            <div><?= Html::a(\Yii::t('modules/users', 'Регистрация'), ['/registration']) ?></div>
        </div>
        <div class="modal-footer">
            <?= SubmitButton::widget(['title' => \Yii::t('modules/users', 'Войти'), 'icon' => 'sign-in', 'style' => 'default']); ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <!-- /.modal-content -->
</div>