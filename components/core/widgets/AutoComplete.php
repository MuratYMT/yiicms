<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.03.2016
 * Time: 17:50
 */

namespace yiicms\components\core\widgets;

use yiicms\components\core\Url;
use yii\helpers\Html;
use yii\web\JsExpression;

class AutoComplete extends \yii\jui\AutoComplete
{
    /**
     * @var bool использовать поле textarea вместо text
     */
    public $useTextArea = false;
    /** @var string с какого адреса грузить данные для подсказок */
    public $url;
    /** @var int минимальная длина текста для того чтобы сработал автокоплит */
    public $minLength = 1;

    public function run()
    {
        if (!isset($this->clientOptions['minLength'])) {
            $this->clientOptions['minLength'] = $this->minLength;
        }

        if (!isset($this->clientOptions['autoFill'])) {
            $this->clientOptions['autoFill'] = true;
        }

        $this->clientOptions['source'] = new JsExpression(
            'function(request, response){window.yii.yiicms.loadTag("' . Url::to([$this->url . '?tag=']) . '", request.term, response);}'
        );

        parent::run();
    }

    /**
     * Renders the AutoComplete widget.
     * @return string the rendering result.
     */
    public function renderWidget()
    {
        $options = $this->options;
        Html::addCssClass($options, 'form-control');
        if ($this->useTextArea) {
            if ($this->hasModel()) {
                return Html::activeTextarea($this->model, $this->attribute, $options);
            } else {
                return Html::textarea($this->name, $this->value, $options);
            }
        } else {
            if ($this->hasModel()) {
                return Html::activeTextInput($this->model, $this->attribute, $options);
            } else {
                return Html::textInput($this->name, $this->value, $options);
            }
        }
    }
}
