<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 01.03.2017
 * Time: 14:29
 */

namespace yiicms\components\core\validators;

class InlineValidator extends \yii\validators\InlineValidator
{
    public function validateAttribute($model, $attribute)
    {
        $method = $this->method;
        if (is_string($method)) {
            $method = [$model, $method];
        }
        $params = $this->params;
        $params['model'] = $model;
        call_user_func($method, $attribute, $params, $this);
    }
}