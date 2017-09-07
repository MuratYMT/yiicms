<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.09.2017
 * Time: 23:16
 */

namespace yiicms\services;

use yiicms\components\core\ArrayHelper;
use yiicms\components\core\cronjob\CronJob;
use yiicms\components\core\DateTime;
use yiicms\components\YiiCms;
use yiicms\models\core\Crontabs;

class CrontabService
{
    /**
     * создает объект работы
     * @param string $jobClass класс работы
     * @return CronJob
     */
    public function createJob($jobClass)
    {
        $refl = new \ReflectionClass($jobClass);
        return $refl->newInstanceArgs();
    }

    /**
     * список доступных классов заданий планирощика
     * @return string[]
     */
    public function availableCronjobs()
    {
        $namespaces = ArrayHelper::asArray(YiiCms::$app->cronjobsNamespaces);
        $result = [];
        foreach ($namespaces as $namespace) {
            $path = \Yii::getAlias(str_replace('\\', '/', "@$namespace"));

            $files = scandir($path, SCANDIR_SORT_ASCENDING);
            foreach ($files as $file) {
                $f = $path . DIRECTORY_SEPARATOR . $file;
                if ($file !== '..' && $file !== '.' && !is_dir($f)) {
                    $class = $namespace . '\\' . pathinfo($file, PATHINFO_FILENAME);
                    if (class_exists($class) && is_subclass_of($class, CronJob::class)) {
                        $result[] = $class;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * выдает key-value массив доступных работ где key класс а value описание
     * @return array
     */
    public function getJobClassDropDown()
    {
        $nameJobs = $this->availableCronjobs();

        $result = [];
        foreach ($nameJobs as $jobClass) {
            $cronJob = $this->createJob($jobClass);
            $result[$jobClass] = $cronJob->description;
        }

        return $result;
    }

    /**
     * выполняет запуск всех заданий
     */
    public function startAll()
    {
        $startTime = $this->startTime();

        $jobs = Crontabs::find()->all();
        foreach ($jobs as $job) {
            if ($this->test($startTime, $job->runTime)) {
                $this->runJob($job);
            }
        }
    }

    /**
     * проверяет запускать или нет задание
     * @param array $startTime время старта
     * @param string $runTemplate шаблон времени старта
     * @return bool|int -1 в случае если задание не выполнялось, true если выполнение успешно
     */
    public function test($startTime, $runTemplate)
    {
        if (empty($runTemplate)) {
            return false;
        }
        $tm = explode(' ', $runTemplate);
        return (is_array($tm) && count($tm) === 5 &&
            $this->testMinutes($tm[0], $startTime) &&
            $this->testHours($tm[1], $startTime) &&
            $this->testDays($tm[2], $startTime) &&
            $this->testMonths($tm[3], $startTime) &&
            $this->testWeekDays($tm[4], $startTime));
    }

    /**
     * выполняет выполнение одного задания
     * @param Crontabs $job задание
     * @return bool
     */
    public function runJob($job)
    {
        $cronJob = $this->createJob($job->jobClass);

        $job->lastRunStart = new DateTime();
        if (!$cronJob->run()) {
            return false;
        }

        $job->lastRunStop = new DateTime();
        $job->save();
        return true;
    }

    /**
     * проверяет соответствует ли шаблон дня текущему времени
     * @param string $day шаблон
     * @param array $startTime время старта
     * @return bool
     */
    private function testDays($day, $startTime)
    {
        $ret = [];
        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) use (&$ret) {
                if ($match[0] === '*') {
                    $ret = range(1, 31);
                } elseif (is_numeric($match[0])) {
                    $ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)mb_substr($match[0], 2, 3);
                    $i = $period;
                    while ($i < 31) {
                        $ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    $ret = range($k[0], $k[1]);
                }
                return '';
            },
            $day
        );
        return in_array($startTime['mday'], $ret, true);
    }

    /**
     * проверяет соответствует ли шаблон месяца текущему времени
     * @param string $month шаблон
     * @param array $startTime время старта
     * @return bool
     */
    private function testMonths($month, $startTime)
    {
        $ret = [];
        // если задано словами
        $month = str_ireplace(
            ['dec', 'nov', 'okt', 'sep', 'aug', 'jul', 'jun', 'may', 'apr', 'mar', 'feb', 'jan'],
            [12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1],
            $month
        );

        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) use (&$ret) {
                if ($match[0] === '*') {
                    $ret = range(1, 12);
                } elseif (is_numeric($match[0])) {
                    $ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)substr($match[0], 2, 3);
                    $i = $period;
                    while ($i <= 12) {
                        $ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    $ret = range($k[0], $k[1]);
                }
            },
            $month
        );
        return in_array($startTime['mon'], $ret, true);
    }

    /**
     * проверяет соответствует ли шаблон дня недели текущему времени
     * @param string $weekDay шаблон
     * @param array $startTime время старта
     * @return bool
     */
    private function testWeekDays($weekDay, $startTime)
    {
        $ret = [];
        //если задано словами
        $weekDay = str_ireplace(
            ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
            [7, 1, 2, 3, 4, 5, 6],
            $weekDay
        );
        $weekDay = str_ireplace('0', '7', $weekDay);

        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) use (&$ret) {
                if ($match[0] === '*') {
                    $ret = range(1, 7);
                } elseif (is_numeric($match[0])) {
                    $ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)substr($match[0], 2, 3);
                    $i = $period;
                    while ($i <= 7) {
                        $ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    $ret = range($k[0], $k[1]);
                }
            },
            $weekDay
        );

        return in_array($startTime['wday'], $ret, true);
    }

    /**
     * проверяет соответствует ли шаблон часов текущему времени
     * @param string $hour шаблон
     * @param array $startTime время старта
     * @return bool
     */
    private function testHours($hour, $startTime)
    {
        $ret = [];

        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) use (&$ret) {
                if ($match[0] === '*') {
                    $ret = range(0, 23);
                } elseif (is_numeric($match[0])) {
                    $ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)substr($match[0], 2, 3);
                    $i = 0;
                    while ($i <= 23) {
                        $ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    $ret = range($k[0], $k[1]);
                }
            },
            $hour
        );

        return in_array($startTime['hours'], $ret, true);
    }

    /**
     * проверяет соответствует ли шаблон минут текущему времени
     * @param string $minute
     * @param array $startTime время старта
     * @return bool
     */
    private function testMinutes($minute, $startTime)
    {
        //если */число то для запуска текущая минута должна быть кратна числу в знаменателе
        //если * то задание должно быть запущено в текущую минуту
        //если число1, число2,.. , числоN то проверяем равна ли текущая минута указной
        //если число то сравниваем это число с текущей минутой
        //если число-число то определяем попадает ли текущая минута в этот диапазон
        $ret = [];

        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) use (&$ret) {
                if ($match[0] === '*') {
                    $ret = range(0, 59);
                } elseif (is_numeric($match[0])) {
                    $ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)substr($match[0], 2, 3);
                    $i = 0;
                    while ($i <= 59) {
                        $ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    $ret = range($k[0], $k[1]);
                }
            },
            $minute
        );

        return in_array($startTime['minutes'], $ret, true);
    }

    /**
     * выдает массив getdate
     * @return array
     */
    private function startTime()
    {
        $result = getdate();
        if ($result['wday'] === 0) {
            $result['wday'] = 7;
        }
        return $result;
    }
}
