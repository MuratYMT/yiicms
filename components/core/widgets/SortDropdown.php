<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 19.02.2016
 * Time: 16:40
 */

namespace yiicms\components\core\widgets;

use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\data\Sort;
use yii\helpers\Html;
use yii\helpers\Inflector;

class SortDropdown extends Widget
{
    /**
     * @var Sort сортировщик
     */
    public $sort;

    public $options;

    public function init()
    {
        parent::init();
        if ($this->sort === null) {
            throw new InvalidParamException(self::class . '::sort must be set before run widget');
        }
    }

    public function run()
    {
        $items = ['' => \Yii::t('yiicms', 'Без сортировки')];
        $selection = '';
        $sortAttributes = $this->sort->attributeOrders;

        foreach ($this->sort->attributes as $name => $attribute) {
            $label = isset($attribute['label']) ? $attribute['label'] : Inflector::camel2words($name);

            if (isset($sortAttributes[$name])) {
                $items[$name] = $label . ' +';
                $items['-' . $name] = $label . ' -';

                $selection = $sortAttributes[$name] === SORT_ASC ? $name : '-' . $name;
            } else {
                $items[$name] = $label;
            }
        }

        $name = isset($this->options['name']) ? $this->options['name'] : $this->sort->sortParam;

        return Html::dropDownList($name, $selection, $items, $this->options);
    }
}
