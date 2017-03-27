<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 26.05.2016
 * Time: 10:43
 */

namespace yiicms\components\content;

use yiicms\components\core\Helper;
use yiicms\components\core\TreeHelper;
use yiicms\models\content\Category;
use yii\helpers\Html;
use yii\jui\InputWidget;

class CategoriesParentsWidget extends InputWidget
{
    public function init()
    {
        parent::init();

        if (!isset($this->options['encode'])) {
            $this->options['encode'] = false;
        }
    }

    public function run()
    {
        $result[''] = \Yii::t('modules/admin', '&lt; ' . 'Без родительской категории' . ' &gt;');

        $query = Category::find()->asArray();
        $categories = TreeHelper::build($query->all(), 'categoryId', 'weight');
        /** @var Category[] $categories */
        $categories = Helper::populateArray(Category::class, $categories);

        foreach ($categories as $key => $category) {
            if ($category->levelNod === 1) {
                $result[$category->categoryId] = '&lt; ' . $category->title . ' &gt;';
            } else {
                $result[$category->categoryId] = str_repeat('&nbsp;', ($category->levelNod - 1) * 6) . '|--' . $category->title;
            }
        }

        $options = $this->options;

        if (!isset($options['class'])) {
            $options['class'] = 'form-control';
        }

        return Html::activeDropDownList($this->model, $this->attribute, $result, $options);
    }
}
