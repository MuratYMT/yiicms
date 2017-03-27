<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.09.2015
 * Time: 14:50
 */

use himiklab\colorbox\Colorbox;
use yii\helpers\Html;
use yiicms\components\core\File;
use yiicms\components\core\Url;
use yiicms\models\core\VFolders;

/**
 * @var $this \yii\web\View
 * @var VFolders $currentFolder
 * @var $embedded bool
 */

?>

<?php foreach ($currentFolder->vFiles as $file) :
    /** @var File $file */
    $file = $file->loadedFile->file
    ?>
    <div class="icon-list image">
        <?= Html::a(Html::img($file->asThumbnail($this, 128, 128), ['alt' => $file->title]), $file->asPhotoUrl($this), ['rel' => 'colorbox']); ?>
        <p>
            <?php if ($embedded) : ?>
                <?= Html::checkbox(
                    'sphoto',
                    false,
                    ['class' => 'for-select', 'data-title' => $file->title, 'data-src' => $file->asPhotoUrl($this)]
                ) ?>
            <?php endif; ?>
            <?= $file->title ?>
        </p>
        <div class="manage-box btn-group">
            <?= Html::a(
                '<span class="fa fa-trash-o"> </span>',
                Url::toWithNewReturn(['/filemanager/delete-file', 'fileId' => $file->id]),
                [
                    'title' => \Yii::t('modules/filemanager', 'Удалить'),
                    'class' => 'btn btn-default pull-right',
                    'data-confirm' => \Yii::t('yiicms', 'Удалить файл?'),
                    'data-pjax' => 1,
                    'data-method' => 'post',
                ]
            ); ?>
            <?= Html::a(
                '<span class="fa fa-pencil"> </span>',
                Url::toWithNewReturn(['/filemanager/rename-file', 'fileId' => $file->id]),
                [
                    'title' => \Yii::t('modules/filemanager', 'Переименовать'),
                    'class' => 'btn btn-default pull-right',
                    'data-pjax' => 0,
                ]
            ); ?>
        </div>
    </div>
<?php endforeach; ?>
<?= Colorbox::widget([
    'targets' => [
        'a[rel=colorbox]' => [
            'maxWidth' => '90%',
            'maxHeight' => '90%',
            'speed' => 500,
            'fadeOut' => 300,
        ],
    ],
    'coreStyle' => 3,
]); ?>
