<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 04.09.2015
 * Time: 8:42
 */
use kartik\file\FileInputAsset;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\models\core\Settings;
use yiicms\modules\admin\controllers\PagesController;
use yiicms\modules\admin\models\pages\LoadImage;

/**
 * @var $this \yii\web\View;
 * @var $loadModel LoadImage
 */

FileInputAsset::register($this);

$this->registerJs(
    '$("#' . Html::getInputId($loadModel, 'uFiles[]') . '").on("filebatchuploadcomplete", function (event, files, extra) {
        $("#' . Html::getInputId($loadModel, 'uFiles[]') . '").fileinput("clear");
     });'
);
$this->registerJs(
/** @lang JavaScript */
    '$("#' . Html::getInputId($loadModel, 'uFiles[]') . '").on("fileuploaded", function (event, files, extra) {
        var image = files["response"]["image"];     
        window.yii.yiicms.attachImage("' . PagesController::PANEL_LOADED_IMAGES . '", image, "' . \Yii::t('yiicms', 'Удалить файл?') . '");
     });'
); ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('yiicms', 'Загрузка файлов') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <?= FileInput::widget([
            'model' => $loadModel,
            'attribute' => 'uFiles',
            'language' => \Yii::$app->language,
            'options' => [
                'multiple' => true,
            ],
            'pluginOptions' => [
                'allowedExtensions' => Settings::get('core.filemanager.imageFileExtension'),
                'browseClass' => 'btn btn-primary',
                'uploadClass' => 'btn btn-danger',
                'showRemove' => false,
                //'showUpload' => false,
                'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                'browseLabel' => \Yii::t('yiicms', 'Выбрать изображения для загрузки'),
                'uploadUrl' => Url::to(['/admin/pages/load-images']),
                'uploadAsync' => $loadModel->uploadAsync,
            ],
        ]) ?>
    </div>
</div>

