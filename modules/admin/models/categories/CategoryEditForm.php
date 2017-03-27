<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 18.08.2015
 * Time: 11:16
 */

namespace yiicms\modules\admin\models\categories;

use yiicms\models\content\Category;
use yii\web\NotFoundHttpException;

/**
 * Class CategoryEditForm
 * @package yiicms\modules\admin\models\pages
 */
class CategoryEditForm extends Category
{

    /**
     * @param int $parentId родительская категория для новой категорий
     * @return CategoryEditForm
     * @throws NotFoundHttpException
     */
    public static function showNew($parentId)
    {
        if ((int)$parentId !== 0) {
            self::findModel($parentId);
        }
        $model = new self;
        $model->parentId = $parentId;
        $model->scenario = self::SC_EDIT;
        return $model;
    }

    /**
     * @param int $categoryId идентификатор редактируемой категории
     * @return null|\yiicms\models\content\Category
     * @throws NotFoundHttpException
     */
    public static function showEdit($categoryId)
    {
        $model = self::findModel($categoryId);
        $model->scenario = self::SC_EDIT;
        return $model;
    }

    private static function findModel($categoryId)
    {
        $model = self::findOne(['categoryId' => $categoryId]);
        if ($model === null) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
}
