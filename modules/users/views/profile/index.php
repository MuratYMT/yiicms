<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 02.07.2015
 * Time: 11:42
 */
use yii\bootstrap\ActiveForm;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\components\core\widgets\TimeZones;
use yiicms\models\core\Users;

/**
 * @var $this \yii\web\View
 * @var $model Users
 */

?>
<div class="row profile">
    <?= $this->render('_profile-info', ['model' => $model]); ?>
    <!-- /.col -->
    <div class="col-md-9">
        <div class="nav-tabs-custom">
            <?= $this->render('_tab-header') ?>
            <div class="tab-content">
                <!-- /.tab-pane -->
                <div class="tab-pane active">
                    <?php $activeForm = ActiveForm::begin(['layout' => 'horizontal']); ?>
                    <?php if (\Yii::$app->user->can('ProfileEdit', ['profileUserId' => $model->userId])) : ?>
                        <section>
                            <h3><?= \Yii::t('modules/users', 'Регистрационные данные'); ?></h3>
                            <?= $activeForm->field($model, 'email')->textInput(['disabled' => 1]); ?>
                            <?= $activeForm->field($model, 'login')->textInput(['disabled' => 1]); ?>
                        </section>
                    <?php endif; ?>
                    <section>
                        <h3><?= \Yii::t('modules/users', 'Контакты'); ?></h3>
                        <?= $activeForm->field($model, 'fio')->textInput(); ?>
                        <?= $activeForm->field($model, 'phone')->textInput(); ?>
                        <?= $activeForm->field($model, 'publicEmail')->textInput(); ?>
                        <?= $activeForm->field($model, 'skype')->textInput(); ?>
                        <?= $activeForm->field($model, 'mailAgent')->textInput(); ?>
                        <?= $activeForm->field($model, 'icq')->textInput(); ?>
                    </section>
                    <section>
                        <h3><?= \Yii::t('modules/users', 'О мне'); ?></h3>
                        <?= $activeForm->field($model, 'education')->textarea(); ?>
                        <?= $activeForm->field($model, 'work')->textarea(); ?>
                        <?= $activeForm->field($model, 'interests')->textarea(); ?>
                        <?= $activeForm->field($model, 'about')->textarea(); ?>
                        <h3><?= \Yii::t('modules/users', 'Местонахождение'); ?></h3>
                        <?= $activeForm->field($model, 'timeZone')->widget(TimeZones::class, ['options'=>['class'=>'form-control']]); ?>
                        <?= $activeForm->field($model, 'location')->textInput(); ?>
                        <!-- /.form-group -->
                    </section>
                    <section>
                        <h3><?= \Yii::t('modules/users', 'Социальные сети'); ?></h3>
                        <?= $activeForm->field($model, 'twitter')->textInput(); ?>
                        <?= $activeForm->field($model, 'facebook')->textInput(); ?>
                        <?= $activeForm->field($model, 'vkontakte')->textInput(); ?>
                        <!-- /.form-group -->
                    </section>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <?= SubmitButton::widget(['align' => 'left']); ?>
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
