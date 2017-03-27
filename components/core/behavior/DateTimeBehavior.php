<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.12.2016
 * Time: 14:54
 */

namespace yiicms\components\core\behavior;

use yiicms\components\core\DateTime;

class DateTimeBehavior extends AttributeConversionBehavior
{
    const FORMAT_DATE = 'date';
    const FORMAT_DATETIME = 'time';

    /** @var string в каком формате хранится только дата или дата и время. по умолчанию только дата */
    public $format = self::FORMAT_DATE;

    public function afterFind()
    {
        foreach ($this->attributes as $modelField) {
            $value = $this->owner->$modelField;
            $this->owner->$modelField = !empty($value) ? new DateTime($value, new \DateTimeZone('UTC')) : null;
        }
    }

    public function beforeUpdate()
    {
        foreach ($this->attributes as $modelField) {
            $format = $this->format === self::FORMAT_DATE ? DateTime::DATE_FORMAT : DateTime::DATETIME_FORMAT;
            $value = $this->owner->$modelField;
            $this->owner->$modelField = !empty($value) ? DateTime::convertToDbFormat($value, null, $format) : null;
        }
    }
}