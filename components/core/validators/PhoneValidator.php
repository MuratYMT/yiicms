<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 08.02.2016
 * Time: 14:38
 */

namespace yiicms\components\core\validators;

use yii\base\NotSupportedException;
use yii\validators\StringValidator;

class PhoneValidator extends StringValidator
{
    public $illegalMessage;
    public $pattern = '/^\+?([87](?!95[4-79]|99[^2457]|907|94[^0]|336)([348]\d|9[0-689]|7[07])\d{8}|[1246]\d{9,13}|5[1-46-9]\d{8,12}|55[1-9]\d{9}|500[56]\d{4}|5016\d{6}|5068\d{7}|502[45]\d{7}|5037\d{7}|50[457]\d{8}|50855\d{4}|509[34]\d{7}|376\d{6}|855\d{8}|856\d{10}|85[0-4789]\d{8,10}|8[68]\d{10,11}|8[14]\d{10}|82\d{9,10}|852\d{8}|90\d{10}|96(0[79]|170|13)\d{6}|96[23]\d{9}|964\d{10}|96(5[69]|89)\d{7}|96(65|77)\d{8}|92[023]\d{9}|91[1879]\d{9}|9[34]7\d{8}|959\d{7}|989\d{9}|97\d{8,12}|99[^45]\d{7,11}|994\d{9}|9955\d{8}|380[34569]\d{8}|38[15]\d{9}|375[234]\d{8}|372\d{7,8}|37[0-4]\d{8}|37[6-9]\d{7,11}|30[69]\d{9}|34[67]\d{8}|3[12359]\d{8,12}|36\d{9}|38[1679]\d{8}|382\d{8,9})$/';

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

        $match = preg_match($this->pattern, $value);
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
        $this->max = 20;
        parent::init();

        if ($this->illegalMessage === null) {
            $this->illegalMessage = \Yii::t('yiicms', 'В {attribute} недопустимый номер телефона');
        }
    }
}
