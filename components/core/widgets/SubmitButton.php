<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 11.11.2016
 * Time: 9:51
 */

namespace yiicms\components\core\widgets;

use yii\bootstrap\Widget;
use yii\helpers\Html;

class SubmitButton extends Widget
{
    /** @var string имя кнопки */
    public $name = 'action';
    /** @var string значение кнопки */
    public $value = 'save';

    public $align = 'left';
    /** @var string bootstrap стиль sucess. error, danger, default и т.п. */
    public $style = 'success';
    /** @var  string надпись на кнопке */
    public $title;
    /** @var string иконка на кнопке. Если null иконка не отображается */
    public $icon = 'floppy-o';
    /** @var string текст окна подтверждения */
    public $dataConfirm;

    public function run()
    {
        if (!isset($this->options['name'])) {
            $this->options['name'] = $this->name;
        }
        if (!isset($this->options['value'])) {
            $this->options['value'] = $this->value;
        }
        if (!isset($this->options['class'])) {
            $this->options['class'] = 'btn pull-' . $this->align . ' btn-' . $this->style;
        }

        if ($this->title === null) {
            $this->title = \Yii::t('yiicms', 'Сохранить');
        }

        if ($this->dataConfirm !== null) {
            $this->options['data-confirm'] = $this->dataConfirm;
        }

        echo Html::submitButton((!empty($this->icon) ? '<i class="fa fa-' . $this->icon . '"></i>&nbsp;&nbsp;' : '') . $this->title, $this->options);
    }
}
