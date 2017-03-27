<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 09.02.2016
 * Time: 22:37
 */

namespace yiicms\components\core\validators;

use codemix\localeurls\UrlManager;
use yii\validators\RangeValidator;

class LangValidator extends RangeValidator
{
    public function init()
    {
        /** @var UrlManager $urlManager */
        $urlManager = \Yii::$app->urlManager;

        $this->range = $urlManager->languages;
        parent::init();
    }
}
