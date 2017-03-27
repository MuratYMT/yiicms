<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 27.12.2016
 * Time: 9:40
 */

namespace yiicms\components\core\validators;

use yiicms\components\core\DateTime;
use yiicms\models\core\Users;
use yii\validators\Validator;

class DateTimeValidator extends Validator
{
    const FORMAT_DATE = 'date';
    const FORMAT_DATETIME = 'time';

    /** @var string в какой формат должны преобразовываться входные данные */
    public $format = self::FORMAT_DATE;

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = \Yii::t('yiicms', '{attribute} имеет неверный формат');
        }
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (is_string($value)) {
            try {
                if (\Yii::$app->user->isGuest) {
                    $timezone = \Yii::$app->timeZone;
                } else {
                    /** @var Users $user */
                    $user = \Yii::$app->user->identity;
                    $timezone = $user->timeZone;
                }
                $value = new DateTime($value, $timezone);
            } catch (\Exception $e) {
                $this->addError($model, $attribute, $this->message);
                return;
            }
        }

        if ($value instanceof DateTime) {
            if ($this->format === self::FORMAT_DATE) {
                $value = $value->truncDate(DateTime::DP_DAY);
            }
            $model->$attribute = $value;
            return;
        }

        $this->addError($model, $attribute, $this->message);
    }
}
