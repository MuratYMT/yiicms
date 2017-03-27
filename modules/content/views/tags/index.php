<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 27.01.2016
 * Time: 12:55
 */
use yiicms\components\core\Pagination;
use yiicms\components\core\Url;
use yii\widgets\LinkPager;

/**
 * @var $this \yiicms\components\core\yii\View
 * @var $models \yiicms\models\content\Page[]
 * @var $paging Pagination
 */

?>
<div class="col-md-9 col-sm-9">
    <section id="content">
        <?php foreach ($models as $page) : ?>
            <article class="blog-post">
                <header>
                    <a href="<?= Url::to(['/content/pages', 'slug' => $page->slugFull]) ?>">
                        <h2><?= $page->title; ?></h2>
                    </a>
                </header>
                <figure class="meta">
                    <a href="<?= Url::to(['/users/profile/index', 'userId' => $page->ownerId]) ?>" class="link-icon"><i
                                class="fa fa-user"></i><?= $page->ownerLogin ?> </a>
                    <i class="fa fa-calendar"> </i> <?= \Yii::$app->formatter->asDate($page->createdAt) ?>
                    <div class="tags">
                        <?php foreach ($page->tags as $tag) : ?>
                            <a href="<?= Url::to(['/content/tags', 'slug' => $tag->slug]) ?>"
                               class="tag article"><?= $tag->title ?></a>
                        <?php endforeach; ?>
                    </div>
                </figure>
                <p><?= $page->announce; ?></p>
                <a href="<?= Url::to(['/content/pages', 'slug' => $page->slugFull]) ?>"
                   class="link-arrow"><?= \Yii::t('modules/content', 'Читать дальше'); ?></a>
            </article><!-- /.blog-post -->
        <?php endforeach; ?>
        <div class="center">
            <?= LinkPager::widget(['pagination' => $paging]) ?>
        </div><!-- /.center-->
    </section>
</div>