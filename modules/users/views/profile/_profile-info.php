<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 07.03.2017
 * Time: 10:46
 */
use Imagine\Image\ManipulatorInterface;
use yii\web\View;
use yiicms\models\core\Users;

/**
 * @var $this View
 * @var $model Users
 */
?>

<div class="col-md-3">

    <!-- Profile Image -->
    <div class="box box-primary">
        <div class="box-body box-profile">
            <img class="profile-user-img img-responsive img-circle"
                 src="<?= $model->photo->asThumbnail($this, 128, 128, ManipulatorInterface::THUMBNAIL_OUTBOUND) ?>"
                 alt="User profile picture">

            <h3 class="profile-username text-center"><?= $model->login ?></h3>
            <p class="text-center"><?= Yii::t('yiicms', 'Зарегестрирован {date}', ['date' => Yii::$app->formatter->asDate($model->createdAt)]) ?></p>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->

    <!-- About Me Box -->
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('yiicms', 'О мне') ?></h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <strong><i class="fa fa-book margin-r-5"></i> <?= $model->getAttributeLabel('education') ?></strong>
            <p class="text-muted"><?= $model->education ?></p>
            <hr>
            <strong><i class="fa fa-cog margin-r-5"></i> <?= $model->getAttributeLabel('work') ?></strong>
            <p class="text-muted"><?= $model->work ?></p>
            <hr>
            <strong><i class="fa fa-map-marker margin-r-5"></i> <?= $model->getAttributeLabel('location') ?></strong>
            <p class="text-muted"><?= $model->location ?></p>
            <hr>
            <strong><i class="fa fa-pencil margin-r-5"></i> <?= $model->getAttributeLabel('interests') ?></strong>
            <p><?= $model->interests ?></p>
            <hr>
            <strong><i class="fa fa-file-text-o margin-r-5"></i> <?= $model->getAttributeLabel('about') ?></strong>
            <p><?= $model->about ?></p>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</div>
