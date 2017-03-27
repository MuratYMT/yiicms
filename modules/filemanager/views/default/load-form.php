<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 04.09.2015
 * Time: 8:42
 */
use kartik\widgets\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\CloseButton;
use yiicms\modules\filemanager\models\FileManagerLoadForm;

/**
 * @var $this \yii\web\View;
 * @var $loadModel FileManagerLoadForm
 * @var $currentFolder int
 */
$this->registerJs(
    '$("#' . Html::getInputId($loadModel, 'uFiles[]') . '") . on("filebatchuploadcomplete", function (event, files, extra) {
        document.location.href = "' . Url::decodeReturnUrl() . '";
     });'
); ?>
<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <?= CloseButton::widget(); ?>
    </div>
</div>
<?php $activeForm = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data'],
    'action' => Url::toRoute(['/filemanager', 'folderId' => $currentFolder]),
]); ?>
<div class="row">
    <div class="form-group col-md-12 col-sm-12">
        <?= $activeForm->field($loadModel, 'uFiles[]')->widget(
            FileInput::class,
            [
                'language' => \Yii::$app->language,
                'options' => [
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'allowedExtensions' => FileManagerLoadForm::allowedExtension(),
                    'allowedPreviewTypes' => ['image'],
                    'browseClass' => 'btn btn-primary',
                    'uploadClass' => 'btn btn-danger',
                    'showRemove' => false,
                    //'showUpload' => false,
                    'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                    'browseLabel' => \Yii::t('modules/filemanager', 'Выбрать файлы для загрузки'),
                    'uploadUrl' => Url::toRoute(['/filemanager/load-files', 'folderId' => $currentFolder]),
                    'uploadAsync' => $loadModel->uploadAsync,
                ],
            ]
        ) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
<!-- /.modal-content -->
