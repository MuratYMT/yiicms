<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 21.02.2017
 * Time: 8:52
 */

namespace yiicms\components\core\widgets;

use yii\bootstrap\InputWidget;
use yii\helpers\Html;
use yiicms\assets\FontawesomeIconPickerAsset;

class FontawesomeIconPicker extends InputWidget
{
    /**
     * @inheritDoc
     */
    public function run()
    {
        FontawesomeIconPickerAsset::register($this->view);

        $this->view->registerJs('$("#' . Html::getInputId($this->model, $this->attribute) . '").iconpicker();');

        $options = $this->options;

        if (!isset($options['class'])) {
            $options['class'] = 'form-control';
        }

        echo Html::activeTextInput($this->model, $this->attribute, $options);

    }
}