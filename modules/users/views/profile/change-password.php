<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 17:13
 */

use yiicms\components\core\widgets\SubmitButton;
use yii\bootstrap\ActiveForm;
use yiicms\modules\users\models\ChangePasswordForm;

/**  @var $this \yii\web\View */
/** @var $form  ChangePasswordForm */
?>

<div class="row profile">
    <?= $this->render('_profile-info', ['model' => $form->user]); ?>
    <!-- /.col -->
    <div class="col-md-9">
        <div class="nav-tabs-custom">
            <?= $this->render('_tab-header') ?>
            <div class="tab-content">
                <!-- /.tab-pane -->

                <div class="tab-pane active">
                    <?php $activeForm = ActiveForm::begin(['layout' => 'horizontal']); ?>
                    <?php if ($form->getUser()->userId === \Yii::$app->user->id) : ?>
                        <?= $activeForm->field($form, 'oldPassword')->passwordInput(); ?>
                    <?php endif; ?>
                    <?= $activeForm->field($form, 'password')->passwordInput(); ?>
                    <?= $activeForm->field($form, 'password2')->passwordInput(); ?>
                    <!-- /.form-group -->
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <?= SubmitButton::widget([
                                'title' => \Yii::t('modules/users', 'Сменить пароль'),
                                'icon' => 'exchange',
                                'align' => 'left',
                            ]); ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div>
        <!-- /.nav-tabs-custom -->
    </div>
    <!-- /.col -->
</div>