<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 15.10.2015
 * Time: 8:24
 */
use romkaChev\yii2\swiper\Swiper;
use yii\helpers\Html;
use yii\web\View;
use yiicms\components\core\Url;

/**
 * @var $this View
 * @var $page \yiicms\models\content\Page
 */

$slideItems = [];
foreach ($page->images as $image) {
    $slideItems[] = [
        'content' => [
            Html::img(
                $image->asThumbnail($this, 900, 500),
                ['style' => 'width:100%'])
        ]
    ];
}

?>

<!-- Content -->
<div class="col-md-9 col-sm-9">
    <div class="blog-post">
        <?= Swiper::widget([
            'items' => $slideItems,
            'behaviours' => [
                Swiper::BEHAVIOUR_PAGINATION,
                Swiper::BEHAVIOUR_PREV_BUTTON,
                Swiper::BEHAVIOUR_NEXT_BUTTON,
            ],
            'containerOptions' => [
                'class' => 'button-row',
            ],
            'pluginOptions' => [
                'autoHeight' => true,
                'grabCursor' => true,
                'centeredSlides' => true,
                'slidesPerView' => 'auto',
                'effect' => 'slide',
                'autoplay' => 2500,
                'loop' => true,
                'fade' => [
                    'crossFade' => false
                ],
                'coverflow' => [
                    'rotate' => 50,
                    'stretch' => 0,
                    'depth' => 100,
                    'modifier' => 1,
                    'slideShadows' => true,
                ]
            ]
        ]) ?>
        <div class="meta">
            <a href="<?= Url::to(['/profile', 'userId' => $page->ownerId]) ?>" class="link-icon"><i
                        class="fa fa-user"></i><?= $page->ownerLogin; ?></a>
            <a href="#" class="link-icon"><i
                        class="fa fa-calendar"></i><?= \Yii::$app->formatter->asDatetime($page->publishedAt) ?></a>

            <div class="tags">
                <?php foreach ($page->tags as $tag) : ?>
                    <a href="<?= Url::to(['/content/tags', 'slug' => $tag->slug]) ?>"
                       class="tag article"><?= $tag->title ?></a>
                <?php endforeach ?>
            </div>
        </div>
        <p><b><?= $page->announce ?> </b></p>
        <?= $page->pageText ?>
    </div>
    <!-- /#content -->
    <?= $this->render('_comments', ['page' => $page]); ?>
    <!-- /#comments -->
</div><!-- /.col-md-9 -->
<!-- end Content -->
