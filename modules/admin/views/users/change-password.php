<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.07.2015
 * Time: 11:45
 */
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CancelButton;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\modules\admin\models\users\ChangePasswordForm;

/**
 * @var $this \yii\web\View
 * @var $model  ChangePasswordForm
 * @var $userId int
 */
?>


<div class="row">
    <div class="col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?php $form = ActiveForm::begin(['layout' => 'horizontal', 'options' => ['data-pjax' => 1]]); ?>
        <div class="row">
            <div class="form-group col-md-12 col-sm-12">
                <?= $form->field($model, 'password')->passwordInput(); ?>
                <?= $form->field($model, 'password2')->passwordInput(); ?>
            </div>
        </div>
        <div class="row button-row">
            <div class="col-sm-offset-3 col-sm-5">
                <?= CancelButton::widget(); ?>
                <?= SubmitButton::widget(['title' => \Yii::t('yiicms', 'Сменить пароль'), 'icon' => 'exchange']); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <?php Pjax::end() ?>
    </div>
</div>