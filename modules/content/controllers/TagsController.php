<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.10.2015
 * Time: 8:11
 */

namespace yiicms\modules\content\controllers;

use yiicms\components\core\Pagination;
use yii\web\Controller;
use yiicms\models\content\Tag;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TagsController extends Controller
{
    /**
     * страница с материалами с указанным тегом
     * @param $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($slug)
    {
        $tag = Tag::findBySlug($slug);
        if ($tag === null) {
            throw new NotFoundHttpException;
        }

        $this->view->title = \Yii::t('modules/content', 'Страницы с тегом {tag}', ['tag' => $tag->title]);

        $query = $tag->visiblePagesForUser()->orderBy(['[[p.createdAt]]' => SORT_DESC]);

        $countQuery = clone $query;
        $paging = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($paging->offset)
            ->limit($paging->limit)
            ->all();

        return $this->render('index', ['models' => $models, 'paging' => $paging]);
    }

    /**
     * экшен для автокоплита тегов
     * @param string $tag набираемый пользователем текст
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionSearch($tag)
    {
        if (!is_string($tag)) {
            throw new BadRequestHttpException;
        }
        $tags = (new Query())->select('title')
            ->from(Tag::tableName())
            ->where([Tag::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'title', $tag . '%', false])
            ->limit(10)
            ->orderBy(['title' => SORT_ASC])
            ->column(Tag::getDb());
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return json_encode(['result' => $tags], JSON_FORCE_OBJECT);
    }
}
