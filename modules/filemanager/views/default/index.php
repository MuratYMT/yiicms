<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.09.2015
 * Time: 9:06
 */

use kartik\file\FileInputAsset;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\filemanager\FolderDropDown;
use yiicms\models\core\Settings;
use yiicms\models\core\VFiles;
use yiicms\models\core\VFolders;

/**
 * @var \yii\web\View $this
 * @var VFolders $currentFolder
 * @var VFiles[] $files
 * @var int $embedded
 */

FileInputAsset::register($this);

if ($embedded) {
    Yii::$app->controller->layout = '@theme/views/layouts/clean';
}

?>
<?php Pjax::begin(['options' => ['class' => 'filemanager']]) ?>
<?php Alert::widget() ?>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="dropdown">
                <?= Html::button(
                    $currentFolder->title . ' <span class="fa fa-chevron-down"> </span>',
                    [
                        'class' => 'btn btn-primary',
                        'data-toggle' => 'dropdown',
                        'aria-haspopup' => 'true',
                        'aria-expanded' => 'false',
                        'style' => 'width:100%',
                    ]
                ); ?>
                <?= FolderDropDown::widget(['folder' => $currentFolder, 'embedded' => $embedded]) ?>
            </div>
        </div>
    </div>
    <!-- отображение содержимого каталога -->
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <!-- подкаталоги -->
            <?= $this->render('_folders-list', ['currentFolder' => $currentFolder, 'embedded' => $embedded]); ?>
            <!-- файлы -->
            <?= $this->render('_files-list', ['currentFolder' => $currentFolder, 'embedded' => $embedded]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <?= Html::a(
                '<i class="fa fa-upload"> </i> ' . \Yii::t('modules/filemanager', 'Загрузить файлы'),
                Url::toWithNewReturn(['/filemanager/load-files', 'folderId' => $currentFolder->folderId]),
                ['class' => 'btn pull-left btn-success', 'data-pjax' => 0]
            ); ?>
            <?php if ($embedded) : ?>
                <?= Html::a(
                    '<i class="fa fa-check"> </i> ' . \Yii::t('modules/filemanager', 'Выбрать отмеченные'),
                    '#',
                    [
                        'class' => 'btn pull-right btn-success',
                        'onclick' => 'window.yii.yiicms.insertImage("' .
                            implode('|', Settings::get('core.filemanager.imageFileExtension')) . '"); return false;',
                    ]
                ); ?>
            <?php endif; ?>
            <?= Html::a(
                '<i class="fa fa-plus"> </i> ' . \Yii::t('modules/filemanager', 'Создать каталог'),
                Url::toWithNewReturn(['/filemanager/add-folder', 'parentFolderId' => $currentFolder->folderId]),
                ['class' => 'btn pull-right btn-primary', 'data-pjax' => 0]
            ); ?>
        </div>
    </div>
<?php Pjax::end() ?>