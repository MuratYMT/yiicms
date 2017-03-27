<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 31.01.2017
 * Time: 16:54
 */

namespace yiicms\components\content;

use yiicms\components\core\ArrayHelper;
use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yiicms\models\content\Page;
use yii\validators\Validator;

class CategoryValidator extends Validator
{
    /**
     * @param Page $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if ($model->hasErrors()) {
            return;
        }
        $oldCategories = $model->oldCategoriesIds;
        $newCategories = $model->categoriesIds;

        if (!$model->isNewRecord && !$model->can(CategoryPermission::PAGE_EDIT, $newCategories)) {
            $model->addError('categoriesIds', \Yii::t('modules/content', 'У вас нет права на редактирование этой страницы'));
        }

        /**
         * @var int[] $added
         * @var int[] $removed
         */
        list($added, $removed) = ArrayHelper::diffValues($newCategories, $oldCategories);

        foreach ($removed as $categoryId) {
            if (!$model->can(CategoryPermission::PAGE_DELETE, $categoryId)) {
                $category = Category::findOne($categoryId);
                $model->addError(
                    'categoriesIds',
                    \Yii::t('modules/content', 'У вас нет прав на удаление страницы из категории "{cat}"', ['cat' => $category->title])
                );
                return;
            }
        }

        foreach ($added as $categoryId) {
            if (!$model->can(CategoryPermission::PAGE_ADD, $categoryId)) {
                $category = Category::findOne($categoryId);
                $model->addError(
                    'categoriesIds',
                    \Yii::t('modules/content', 'У вас нет прав на добавление страниц в категорию "{cat}"', ['cat' => $category->title])
                );
                return;
            }
        }
    }
}