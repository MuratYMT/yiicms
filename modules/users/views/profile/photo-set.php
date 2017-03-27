<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.07.2015
 * Time: 20:42
 */

use kartik\widgets\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yiicms\components\core\Url;
use yiicms\models\core\Settings;
use yiicms\modules\users\models\PhotoSetForm;

/**
 * @var $this \yii\web\View
 * @var $form PhotoSetForm
 * @var $content string
 */

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
                    <?php if (!$form->user->photo->isEmpty) : ?>
                        <!-- /.form-group -->
                        <div class="form-group row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <?= Html::a(
                                    '<i class="fa fa-trash"> </i> ' . \Yii::t('modules/users', 'Удалить'),
                                    Url::toWithNewReturn(['/profile/photo-del']),
                                    [
                                        'class' => 'btn btn-warning pull-right',
                                        'data-post' => 1,
                                        'data-message' => \Yii::t('yiicms', 'Удалить фотографию?'),
                                    ]
                                ) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group row">
                        <div class="col-sm-12">

                            <img width="100%" alt="" class="image" src="<?= $form->getUser()->photo->asPhotoUrl($this) ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <?php $activeForm = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
                            <?= $activeForm->field($form,
                                'file', ['enableLabel' => false])->widget(
                                FileInput::class,
                                [
                                    'language' => \Yii::$app->language,
                                    'pluginOptions' => [
                                        'allowedExtensions' => Settings::get('core.filemanager.imageFileExtension'),
                                        'browseClass' => 'btn btn-primary',
                                        'uploadClass' => 'btn btn-danger',
                                        'showRemove' => false,
                                        //'showUpload' => false,
                                        'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                                        'browseLabel' => \Yii::t('modules/users', 'Выбрать фото'),
                                        'uploadLabel' => \Yii::t('modules/users', 'Установить'),
                                    ],
                                ]
                            ) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div>
        <!-- /.nav-tabs-custom -->
    </div>
    <!-- /.col -->
</div>
