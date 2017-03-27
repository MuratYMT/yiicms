<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25.09.2015
 * Time: 10:39
 */

namespace yiicms\components\core\validators;

use yii\base\NotSupportedException;
use yii\validators\StringValidator;

class TitleValidator extends StringValidator
{
    public $illegalMessage;

    /**
     * @inheritDoc
     * @throws NotSupportedException
     */
    protected function validateValue($value)
    {
        $res = parent::validateValue($value);
        if ($res !== null) {
            return $res;
        }

        $match = preg_match('/^[\w \.\-]+$/u', $value);
        if ($match === 0 || $match === false) {
            return [$this->illegalMessage, []];
        }

        return null;
    }

    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        $result = $this->validateValue($model->$attribute);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
    }

    public function init()
    {
        parent::init();

        if ($this->illegalMessage === null) {
            $this->illegalMessage = \Yii::t('yiicms', 'В {attribute} должны модержаться только буквы, цифры и следующие символы "_.-"');
        }
    }
}
