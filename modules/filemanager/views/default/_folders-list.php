<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.09.2015
 * Time: 14:44
 */
use yii\helpers\Html;
use yiicms\components\core\fileicons\IconsAsset;
use yiicms\components\core\Url;
use yiicms\models\core\VFolders;

/**
 * @var \yii\web\View $this
 * @var VFolders $currentFolder
 * @var bool $embedded
 */

$iconAsset = IconsAsset::register($this);

?>
<?php foreach ($currentFolder->childFolders as $folder) : ?>
    <div class="icon-list">
        <?= Html::a(
            Html::img($iconAsset->baseUrl . '/folder.png', ['style' => 'width: 100%']),
            Url::toRoute(['/filemanager', 'folderId' => $folder->folderId, 'embedded' => $embedded])
        ) ?>
        <p><?= $folder->title ?></p>
        <div class="manage-box btn-group">
            <?= Html::a(
                '<span class="fa fa-trash-o"> </span>',
                Url::toWithNewReturn(['/filemanager/delete-folder', 'folderId' => $folder->folderId]),
                [
                    'title' => \Yii::t('modules/filemanager', 'Удалить'),
                    'class' => 'btn btn-default pull-right',
                    'data-method' => 'post',
                    'data-confirm' => \Yii::t('yiicms', 'Удалить каталог и все его содержимое?'),
                    'data-pjax' => 0
                ]
            ); ?>
            <?= Html::a(
                '<span class="fa fa-pencil"> </span>',
                Url::toWithNewReturn(['/filemanager/rename-folder', 'folderId' => $folder->folderId]),
                ['title' => \Yii::t('modules/filemanager', 'Переименовать'), 'class' => 'btn btn-default pull-right', 'data-pjax' => 0]
            ); ?>
        </div>
    </div>
<?php endforeach; ?>