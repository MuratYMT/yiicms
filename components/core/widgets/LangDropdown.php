<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.09.2015
 * Time: 12:04
 */

namespace yiicms\components\core\widgets;

use codemix\localeurls\UrlManager;
use yii\bootstrap\InputWidget;
use yii\bootstrap\Html;

class LangDropdown extends InputWidget
{
    /**
     * @inheritDoc
     */
    public function run()
    {
        /** @var UrlManager $urlManager */
        $urlManager = \Yii::$app->urlManager;
        $langs = $urlManager->languages;

        $options = $this->options;

        if (!isset($options['class'])) {
            $options['class'] = 'form-control';
        }

        echo Html::activeDropDownList($this->model, $this->attribute, array_combine($langs, $langs), $options);
    }
}
