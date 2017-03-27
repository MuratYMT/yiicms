<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 30.01.2015
 * Time: 14:06
 */

namespace yiicms\components\core;

use DateInterval;
use yii\helpers\FormatConverter;

/**
 * Class DateTime
 * @package sfw\fw\components
 */
class DateTime extends \DateTime
{
    const DP_MINUTE = 'minute';
    const DP_HOUR = 'hour';
    const DP_DAY = 'day';
    const DP_MONTH = 'month';
    const DP_YEAR = 'year';

    /**
     * внутренний формат даты/времени
     */
    const DATETIME_FORMAT = 'yyyy-MM-dd HH:mm:ss';
    /**
     * внутренний формат даты
     */
    const DATE_FORMAT = 'yyyy-MM-dd';

    /**
     * @param string|integer $time время в любом поддерживаемом формате или Unix TimeStamp
     * @param \DateTimeZone|string $timezone часовой пояс
     */
    public function __construct($time = 'now', $timezone = null)
    {
        if (is_numeric($time)) {
            //UNIX Timestamp
            $time = '@' . $time;
            $timezone = null;
        } elseif ($timezone === null) {
            $timezone = new \DateTimeZone('UTC');
        } elseif (is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        }

        parent::__construct($time, $timezone);
    }

    /**
     * Set the TimeZone associated with the DateTime
     * @param \DateTimeZone|string $timezone часовой пояс
     * @return $this
     * @link http://php.net/manual/en/datetime.settimezone.php
     */
    public function setTimezone($timezone)
    {
        if (is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        }
        return parent::setTimezone($timezone);
    }

    /**
     * добавляет к дате интервал времени (для уменьшения перед $delta надо поставить "-")
     * Y    years P1Y
     * M    months
     * D    days P3D
     * W     weeks. These get converted into days, so can not be combined with D.
     * H    hours PT1H
     * M    minutes PT10M
     * S    seconds
     * @param DateInterval|string $interval какой интервал добавить
     * @return $this
     */
    public function add($interval)
    {
        if ($interval instanceof DateInterval) {
            parent::add($interval);
        } else {
            /** @noinspection OffsetOperationsInspection */
            if ($interval{0} === '-') {
                $this->sub(new \DateInterval(ltrim($interval, '-')));
            } else {
                parent::add(new \DateInterval($interval));
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        /** @noinspection MagicMethodsValidityInspection */
        return \Yii::$app->formatter->asDatetime($this, self::DATETIME_FORMAT);
    }

    /**
     * @param string $format
     * @return string
     */
    public function asDateString($format = null)
    {
        if ($format === null) {
            $format = self::DATE_FORMAT;
        }
        $date = clone $this;
        $date->truncDate(self::DP_DAY);
        return $date->format($format);
    }

    /**
     * форматирует дату в соответствии с указанным форматом
     * @param string $format формат в стандарте ICU. для использования стандарта PHP должно начинаться с php:
     * @return string
     */
    public function format($format)
    {
        $formatter = \Yii::$app->formatter;
        if (strncmp($format, 'php:', 4) === 0) {
            $format = FormatConverter::convertDatePhpToIcu(substr($format, 4));
        }
        return (new \IntlDateFormatter($formatter->locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $this->getTimezone(), null, $format))
            ->format($this);
    }

    /**
     * Возвращает разницу между двумя датами
     * @param DateTime $datetime2
     * @return integer
     */
    public function diffSecond($datetime2)
    {
        return $datetime2->getTimestamp() - $this->getTimestamp();
    }

    /**
     * отбрасывает дату до указанного поля
     * @param string $part до какого поля отбросить дату
     * @return DateTime
     */
    public function truncDate($part)
    {
        switch ($part) {
            case self::DP_MINUTE:
                $format = 'yyyy-MM-dd HH:mm';
                break;
            case self::DP_HOUR:
                $format = 'yyyy-MM-dd HH:00';
                break;
            case self::DP_DAY:
                $format = 'yyyy-MM-dd';
                break;
            case self::DP_MONTH:
                $format = 'yyyy-MM-01';
                break;
            case self::DP_YEAR:
                $format = 'yyyy-01-01';
                break;
            default:
                $format = self::DATETIME_FORMAT;
                break;
        }

        parent::setTimestamp((new DateTime($this->format($format)))->getTimestamp());

        return $this;
    }

    /**
     * определяет сколько дней между датами
     * если $to_date < $from_date выдает отрицательные значения
     * @param string|DateTime $toDate до какой даты
     * @return integer количество дней
     */
    public function daysDiff($toDate)
    {
        $toDate = $toDate instanceof DateTime ? $toDate : new DateTime($toDate);
        $days = $this->diff($toDate, false)->days;
        return $this < $toDate ? $days : -$days;
    }

    /**
     * @return string дата в строковом формате во внутреннем формате
     */
    public function getIso()
    {
        return $this->format(DateTime::DATETIME_FORMAT);
    }

    /**
     * функция определяет это последний день в месяце или нет
     * @return bool
     */
    public function getIsLastDayInMonth()
    {
        return (int)$this->format('d') === $this->getDaysInMonth();
    }

    /**
     * определяет переданная дата это первый день месяца или нет
     * @return bool
     */
    public function getIsFirstDayInMonth()
    {
        return (int)$this->format('d') === 1;
    }

    /** @var array массив с количеством дней в каждом месяце */
    private static $_dayInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    /**
     * функция определяет сколько дней в месяце к которому принадлежит $date
     * @return integer количество дней в месяце
     */
    public function getDaysInMonth()
    {
        $year = $this->format('yyyy');

        //в високосные года добавляем 1 день в февраль
        if (($year % 4) === 0 && ($year % 400) !== 0) {
            self::$_dayInMonth[1]++;
        }
        return self::$_dayInMonth[$this->format('M') - 1];
    }

    /**
     * @var self
     */
    private static $_runTime;

    /**
     * функция возвращает текущее время сервера в UTC в формате ISO
     * в рамках одного запуска скрипта выдает всегда одно и оже время, время первого обращения
     * @return DateTime
     */
    public static function runTime()
    {
        if (self::$_runTime === null) {
            self::$_runTime = new DateTime('now', \Yii::$app->formatter->timeZone);
        }
        return clone self::$_runTime;
    }

    private static $_runDate;

    /**
     * функция возвращает текущую дату сервера в UTC в формате ISO
     * в рамках одного запуска скрипта выдает всегда одно и оже время, время первого обращения
     * @return DateTime
     */
    public static function runDate()
    {
        if (self::$_runDate === null) {
            $runTime = self::runTime();
            self::$_runDate = clone  $runTime;
            self::$_runDate->truncDate(self::DP_DAY);
        }
        return clone self::$_runDate;
    }

    /**
     * выполняет преобразование даты/времени в строковый формат (в часовом поясе UTC) пригодный для хранения в БД
     * @param string|\DateTime $datetime
     * @param string|\DateTimeZone $timeZone часовой пояс. Если передан объект \DateTime то переданное значение
     * часового пояса игнорируется и берется из свойста объекта \DateTime
     * если передано строковое представление даты/времени и $valueTimeZone=null то используется часовой пояс форматтера
     * @param string $format шаблон форматирования выходных данных по умолчанию self::DATETIME_FORMAT
     * @return string дата/время в формате $format
     */
    public static function convertToDbFormat($datetime, $timeZone = null, $format = null)
    {
        if (empty($datetime)) {
            return null;
        }

        if ($format === null) {
            $format = self::DATETIME_FORMAT;
        }

        $formatter = \Yii::$app->formatter;

        if ($datetime instanceof DateTime) {
            $dateObj = $datetime;
        } else {
            if ($timeZone instanceof \DateTimeZone) {
                $timeZoneObj = $timeZone;
            } elseif ($timeZone === null) {
                $timeZoneObj = new \DateTimeZone($formatter->timeZone);
            } else {
                $timeZoneObj = new \DateTimeZone($timeZone);
            }
            $dateObj = new DateTime($datetime, $timeZoneObj);
        }

        return $dateObj->setTimezone('UTC')->format($format);
    }

    /**
     * определяет сколько дней между датами
     * если $to_date < $from_date выдает отрицательные значения
     * @param string|DateTime $fromDate с какой даты
     * @param string|DateTime $toDate по какую дату
     * @param bool $include включать обе границы диапазона в количество дней
     * @return false|int количество дней false если не удалось определить
     */
    public static function daysBetweenDates($fromDate, $toDate, $include = false)
    {
        $_fromDate = $fromDate instanceof DateTime ? clone $fromDate : new DateTime($fromDate);
        $_toDate = $toDate instanceof DateTime ? clone $toDate : new DateTime($toDate);

        $flip = false;
        if ($_toDate < $_fromDate) {
            $tmp = $_toDate;
            $_toDate = $_fromDate;
            $_fromDate = $tmp;
            $flip = true;
        }

        if ($include) {
            $_fromDate->add('-P1D');
        }

        $days = $_toDate->diff($_fromDate)->days;

        if ($days === false) {
            return false;
        }

        return (int)($flip ? -$days : $days);
    }

    /**
     * определяет сколько дней между датами
     * если $to_date < $from_date выдает отрицательные значения
     * @param string|DateTime $fromDate с какой даты
     * @param string|DateTime $toDate по какую дату
     * @param bool $include включать ли первый день в границы (добавляет 1 день)
     * @return false|int количество дней false если не удалось определить
     */
    public static function monthsBetweenDates($fromDate, $toDate, $include = false)
    {
        $_fromDate = $fromDate instanceof DateTime ? clone $fromDate : new DateTime($fromDate);
        $_toDate = $toDate instanceof DateTime ? clone $toDate : new DateTime($toDate);

        $flip = false;
        if ($_toDate < $_fromDate) {
            $tmp = $_toDate;
            $_toDate = $_fromDate;
            $_fromDate = $tmp;
            $flip = true;
        }

        if ($include) {
            $_toDate->add('P1D');
        }

        $months = $_toDate->diff($_fromDate)->m;
        $days = $_toDate->diff($_fromDate)->d;
        if ($months === false || $days === false) {
            return false;
        }

        $months += $days / $_toDate->getDaysInMonth();

        return (float)($flip ? -$months : $months);
    }

    /**
     * выдает массив дат между $from_day и $to_day включительно например между '2013-01-01' и '2013-01-04'
     * выдаст array('2013-01-01', '2013-01-02', '2013-01-03', '2013-01-04')
     * @param string|DateTime $fromDay дата в любом поддерживаемом формате
     * @param string|DateTime $toDay дата в любом поддерживаемом формате
     * @param bool $firstDayInclude включать ли стартовую дату в результирующий массив
     * @return DateTime[]
     */
    public static function rangeDate($fromDay, $toDay, $firstDayInclude = true)
    {
        $result = [];

        $fromDate = $fromDay instanceof DateTime ? clone $fromDay : new DateTime($fromDay);
        $toDate = $toDay instanceof DateTime ? clone $toDay : new DateTime($toDay);
        $oneDay = new \DateInterval('P1D');

        //если стартовый день не включать то передвигаем дату старнта на 1 день вперед
        $fromDate->truncDate(self::DP_DAY);
        $toDate->truncDate(self::DP_DAY);
        if (!$firstDayInclude) {
            $fromDate->add($oneDay);
        }

        while ($fromDate <= $toDate) {
            $result[] = clone $fromDate;
            $fromDate->add($oneDay);
        }

        return $result;
    }

    /**
     * выдает текущую микросекунду
     */
    public static function getMicrotime()
    {
        $t = gettimeofday();
        return ($t['sec'] - floor($t['sec'] / 10000) * 10000) * 1000 + $t['usec'] / 1000;
    }

    /**
     * выдает интервал времени в человекоудобном формате
     * @param integer $second интервал в секундах
     * @return string
     * @deprecated
     */
    public static function dateInterval($second)
    {
        $d = floor($second / 86400);
        $s = $second - ($d * 86400);
        $h = floor($s / 3600);
        $s -= ($h * 3600);
        $m = floor($s / 60);
        $s -= ($m * 60);

        $ret = $d !== 0 ? $d . \Yii::t('yiicms', ' д, ') : '';
        $ret .= ($h !== 0) ? $h . \Yii::t('yiicms', ' ч, ') : '';
        $ret .= ($m !== 0) ? $m . \Yii::t('yiicms', ' м, ') : '';
        $ret .= ($s !== 0) ? $s . \Yii::t('yiicms', ' с, ') : '';
        return rtrim($ret, ', ');
    }
}
