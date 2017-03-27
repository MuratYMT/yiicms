<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 13.06.2016
 * Time: 14:56
 */

namespace yiicms\components\core\widgets;

use yii\bootstrap\InputWidget;
use yii\helpers\Html;

class TimeZones extends InputWidget
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
        $tz1 = \DateTimeZone::listIdentifiers();
        $tz2 = $tz1;

        array_unshift($tz1, '0');
        array_unshift($tz2, '');

        echo Html::activeDropDownList($this->model, $this->attribute, array_combine($tz1, $tz2), $this->options);
    }
}
