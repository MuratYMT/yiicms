<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 27.01.2016
 * Time: 15:56
 */

namespace yiicms\modules\content\controllers;

use yiicms\components\core\Pagination;
use yii\web\Controller;
use yiicms\models\content\Category;
use yiicms\models\content\Page;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yiicms\modules\content\models\categories\CategoriesSearch;

class CategoriesController extends Controller
{
    /**
     * @param string $slug выдать страницы в определенной категории
     * @param string $action если не указан то выдать страницы только этой категории,
     * 'all' - выдать страницы из вложенных категорий
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionIndex($slug = null, $action = null)
    {
        $category = null;
        if ($slug !== null && null === ($category = Category::findBySlug($slug))) {
            throw new NotFoundHttpException;
        }

        list($paging, $pages) = CategoriesSearch::search($category, $action === 'all', false);

        if ($category !== null) {
            $this->view->title = $category->title;
        }

        return $this->render('index', ['pages' => $pages, 'paging' => $paging]);
    }
}
