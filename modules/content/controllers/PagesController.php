<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 15.10.2015
 * Time: 7:58
 */

namespace yiicms\modules\content\controllers;

use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\models\content\CategoryPermission;
use yiicms\models\content\Page;
use yiicms\modules\content\models\CommentEdit;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class PagesController extends Controller
{
    public function actionIndex($slug)
    {
        $page = Page::findBySlug($slug);
        if ($page === null || !$page->isPublished) {
            throw new NotFoundHttpException;
        }

        if (!$page->can(CategoryPermission::PAGE_READ)) {
            throw new ForbiddenHttpException;
        }

        $this->view->title = $page->title;

        return $this->render('index', ['page' => $page]);
    }

    public function actionCommentAdd($pageId, $parentId = 0)
    {
        if (null === ($page = Page::findOne($pageId))) {
            throw new NotFoundHttpException;
        }

        if (!$page->can(CategoryPermission::COMMENT_ADD)) {
            throw new ForbiddenHttpException;
        }

        $request = \Yii::$app->request;

        $comment = CommentEdit::showNew($page->commentsGroup, $parentId);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $comment->load($request->post()) && $comment->save()) {
            Alert::success(\Yii::t('modules/content', 'Комментарий добавлен'));
            return $this->redirect(Url::to(['/content/pages', 'slug' => $page->slugFull, '#' => 'comment' . $comment->commentId]));
        }

        $this->view->title = \Yii::t('modules/content', 'Добавить комментарий');

        if ($request->isPjax) {
            $this->layout = false;
        }

        return $this->render('_comment-edit', [
            'model' => $comment,
            'action' => Url::to(['/content/pages/comment-add', 'parentId' => $parentId, 'pageId' => $pageId]),
        ]);
    }
}
