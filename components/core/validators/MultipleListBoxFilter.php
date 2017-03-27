<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 30.12.2016
 * Time: 15:46
 */

namespace yiicms\components\core\validators;

use yii\validators\Validator;

class MultipleListBoxFilter extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!is_array($value)) {
            return;
        }

        foreach ($value as $key => $item) {
            if ($this->isEmpty($item)) {
                unset($value[$key]);
            }
        }
        $model->$attribute = $value;
    }
}