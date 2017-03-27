<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 30.09.2015
 * Time: 11:45
 */
use yii\helpers\Html;
use yiicms\models\content\Page;
use yiicms\modules\admin\controllers\PagesController;
use yiicms\modules\admin\models\pages\LoadImage;
use yiicms\modules\admin\models\pages\PageEdit;

/**
 * @var $this \yii\web\View
 * @var $model Page
 * @var $pageId int
 * @var $loadModel LoadImage
 */

$hiddenInputName = Html::getInputName(new PageEdit(), 'imagesIds') . '[]';
//костыль. без этого скрытого поля, если удалить все изображения прикрепленные к странице и сохранить ее, то все изображения останутся
echo Html::hiddenInput($hiddenInputName, '');
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('yiicms', 'Прикрепленные изображения') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="filemanager linked-image" id="<?= PagesController::PANEL_LOADED_IMAGES ?>">
            <?php foreach ($model->images as $image) : ?>
                <?= Html::hiddenInput($hiddenInputName, $image->id) ?>
                <div class="icon-list image">
                    <?= Html::img($image->asThumbnail($this, 128, 128), ['id' => $image->id]); ?>
                    <p>
                        <?= $image->title ?>
                    </p>
                    <div class="manage-box btn-group">
                        <?= Html::a(
                            '<span class="fa fa-trash-o"> </span>',
                            '#',
                            [
                                'title' => \Yii::t('modules/filemanager', 'Удалить'),
                                'class' => 'btn btn-default pull-right',
                                'data-message' => \Yii::t('yiicms', 'Удалить файл?'),
                                'data-id' => $image->id,
                                'data-unlink' => 1,
                            ]
                        ); ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>