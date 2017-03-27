<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 28.04.2016
 * Time: 8:58
 */

namespace yiicms\components\core\widgets;

use yii\bootstrap\InputWidget;
use yii\helpers\Html;

class DropDownList extends InputWidget
{
    /**
     * @var array key-value массив элементов для отображения
     */
    public $items = [];

    public function run()
    {
        $items = $this->items;
        $options = $this->options;

        if (array_key_exists('placeholder', $options)) {
            $placeholder = $options['placeholder'];
            $items = ['' => $placeholder] + $items;
            unset($options['placeholder']);
        }
        echo Html::activeDropDownList($this->model, $this->attribute, $items, $this->options);
    }
}
