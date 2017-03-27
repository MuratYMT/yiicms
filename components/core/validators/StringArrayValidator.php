<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 04.08.2016
 * Time: 14:48
 */

namespace yiicms\components\core\validators;

use yii\validators\StringValidator;

class StringArrayValidator extends StringValidator
{
    public $notArray;

    public function init()
    {
        $this->enableClientValidation = false;
        parent::init();
        if ($this->notArray === null) {
            $this->notArray = \Yii::t('yiicms', '{attribute} must be a string array.');
        }
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!is_array($value)) {
            $value = array_map('trim', explode(',', $value));
        }
        /** @var array $value */
        foreach ($value as $val) {
            if (null !== ($res = parent::validateValue($val))) {
                $message = array_shift($res);
                $this->addError($model, $attribute, $message, $res);
                return;
            }
        }
    }

    /**
     * @param string|array $value
     * @return array|null
     */
    protected function validateValue($value)
    {
        if (!is_array($value)) {
            $value = array_map('trim', explode(',', $value));
        }

        /** @noinspection ForeachSourceInspection */
        foreach ($value as $val) {
            if (null !== ($res = parent::validateValue($val))) {
                return $res;
            }
        }

        return null;
    }
}
