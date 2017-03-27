<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 15.10.2015
 * Time: 14:21
 */

use yii\helpers\Html;
use yii\web\View;
use yiicms\components\core\Url;
use yiicms\models\content\Comment;

/**
 * @var $this View
 * @var $page \yiicms\models\content\Page
 */

$level = 1;
$comments = $page->commentsAsTree;

$this->registerJs('
$(document).on("click", "div[class=\'comment-text\'] a[class=\'reply\']", function($event){
    var $this = $(this),
        container = $("#reply-placeholder-"+$this.data("container")),
        pjaxOptions = {
        container: container,
        scrollTo: false,
        push: false
    };
    if ($this.data("opened") == 1){
        $this.data("opened", 0);
        container.hide();
        container.empty();
        $this.html("<span class=\'fa fa-reply\'></span> ' . \Yii::t('modules/content', 'Ответить') . '");
    } else{
        $this.data("opened", 1);
        container.show();
        $this.html("<span class=\'fa fa-close\'></span> ' . \Yii::t('modules/content', 'Закрыть') . '");
    }
    $.pjax.click($event, pjaxOptions);
    return false;
});
')
?>
<div class="row">
    <header><h2 class="no-border"><?= \Yii::t('modules/content', 'Комментарии') ?></h2></header>
    <?= $this->render('_comment-edit', [
        'model' => new Comment(),
        'action' => Url::to(['/content/pages/comment-add', 'parentId' => 0, 'pageId' => $page->pageId]),
    ]); ?>
    <?php if (count($comments) === 0) : ?>
        <p><i><b><?= \Yii::t('modules/content', 'Комментариев нет') ?></b></i></p>
    <?php endif ?>
    <div class="box-footer box-comments">
        <?php foreach ($comments as $comment) : ?>
            <div class="box-comment" style="margin-left: <?= ($comment->levelNod - 1) * 30 ?>px">
                <img alt="<?= $comment->ownerUser->login ?>" src="<?= $comment->ownerUser->photo->asPhotoUrl($this); ?>">
                <div class="comment-text">
                    <span class="username">
                        <?= $comment->ownerLogin ?>
                        <span class="text-muted pull-right">
                            <?= \Yii::$app->formatter->asRelativeTime($comment->createdAt) ?>
                            <i class="fa fa-calendar"></i>
                        </span>
                    </span>
                    <a name="comment<?= $comment->commentId ?>"></a>
                    <?= $comment->commentText ?>
                    <div style="display: none" id="<?= 'reply-placeholder-' . $comment->commentId ?>"></div>
                    <?= Html::a('<span class="fa fa-reply"></span> ' . \Yii::t('modules/content', 'Ответить'),
                        Url::to(['/content/pages/comment-add', 'parentId' => $comment->commentId, 'pageId' => $page->pageId]),
                        ['data-container' => $comment->commentId, 'class' => 'reply']
                    ) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
