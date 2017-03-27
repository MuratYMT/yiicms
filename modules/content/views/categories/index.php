<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 27.01.2016
 * Time: 12:55
 */
use yiicms\models\content\Tag;
use yii\web\View;
use yiicms\components\core\Pagination;
use yiicms\components\core\Url;
use yii\widgets\LinkPager;

/**
 * @var $this View
 * @var $pages \yiicms\models\content\Page[]
 * @var $paging Pagination
 */

?>
<div class="col-md-9 col-sm-9">
    <?php foreach ($pages as $page) : ?>
        <article class="blog-post">
            <header>
                <a href="<?= Url::to(['/page/' . $page->slugFull]) ?>">
                    <h2><?= $page->title; ?></h2>
                </a>
            </header>
            <ul class="meta">
                <li>
                    <a href="<?= Url::to(['/users/profile/index', 'userId' => $page->ownerId]) ?>" class="link-icon">
                        <i class="fa fa-user"></i> <?= $page->ownerLogin ?>
                    </a>
                </li>
                <li>
                    <i class="fa fa-calendar"> </i> <?= \Yii::$app->formatter->asRelativeTime($page->publishedAt) ?>
                </li>
                <li class="tags">
                    <i class="fa fa-tags"></i>
                    <?= implode(' ,', array_map(function ($tag) {
                        /** @var Tag $tag */
                        return '<a href="' . Url::to(['/tag/' . $tag->slug]) . '" class="tag article">' . $tag->title . '</a>';
                    }, $page->tags)) ?>
                </li>
                <li>
                    <i class="fa fa-comments"></i>
                    <?= $page->commentsCount ?>
                </li>
            </ul>
            <p><?= $page->announce; ?></p>
            <a href="<?= Url::to(['/page/' . $page->slugFull]) ?>"
               class="link-arrow"><?= \Yii::t('modules/content', 'Читать дальше'); ?></a>
        </article><!-- /.blog-post -->
    <?php endforeach; ?>
    <div class="center">
        <?= LinkPager::widget(['pagination' => $paging]) ?>
    </div><!-- /.center-->
</div>