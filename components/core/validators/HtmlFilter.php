<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 09.02.2016
 * Time: 23:04
 */

namespace yiicms\components\core\validators;

use yii\validators\FilterValidator;

class HtmlFilter extends FilterValidator
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->filter = function ($content) {
            if (is_array($content)) {
                $content = json_encode($content, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
            }
            if ($this->isEmpty($content)) {
                return $content;
            }
            return htmlspecialchars($content, ENT_NOQUOTES | ENT_SUBSTITUTE, \Yii::$app ? \Yii::$app->charset : 'UTF-8', false);
        };
        $this->skipOnArray = true;
        parent::init();
    }
}
