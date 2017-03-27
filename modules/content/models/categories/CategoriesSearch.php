<?php

namespace yiicms\modules\content\models\categories;

use yiicms\components\core\Pagination;
use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yiicms\models\content\Page;

/**
 * Created by PhpStorm.
 * User: murat
 * Date: 23.03.2017
 * Time: 9:42
 */
class CategoriesSearch
{
    /**
     * @param Category $category в какой категории искать страницы
     * @param bool $recursive рекурсивный поиск
     * @param bool $toFirst флаг того что выдавать только страницы для первой
     * @param int $sort
     * @return array
     */
    public static function search($category, $recursive, $toFirst, $sort = SORT_DESC)
    {
        $query = Page::wherePublished(Page::query(CategoryPermission::CATEGORY_VIEW, $category, $recursive))
            ->andWhere(['lang' => \Yii::$app->language]);
        if ($toFirst) {
            $query->andWhere(['toFirst' => 1]);
        }

        $query->orderBy([Page::tableName() . '.[[createdAt]]' => $sort]);

        $paging = new Pagination(['totalCount' => (clone $query)->count()]);
        $pages = $query->offset($paging->offset)
            ->limit($paging->limit)
            ->all();
        return [$paging, $pages];
    }
}