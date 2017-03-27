<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 11.11.2016
 * Time: 9:28
 */

namespace yiicms\components\core\widgets;

use yiicms\components\core\Url;
use yii\bootstrap\Widget;
use yii\helpers\Html;

class CloseButton extends Widget
{
    /** @var string CSS класс кнопки */
    public $class = 'btn pull-left btn-warning';

    public $usePjax = false;

    public function run()
    {
        $options = $this->options;
        $options['class'] = $this->class;
        $options['data-pjax'] = $this->usePjax ? 1 : 0;
        if (Url::issetReturn()) {
            echo Html::a('<i class="fa fa-times"> </i> ' . \Yii::t('yiicms', 'Закрыть'), Url::decodeReturnUrl(), $options);
        }
    }
}
