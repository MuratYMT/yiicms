<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yiicms\components\core\ArrayHelper;
use yiicms\components\core\behavior\DateTimeBehavior;
use yiicms\components\core\cronjob\CronJob;
use yiicms\components\core\DateTime;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\yii\CommonApplicationTrait;

/**
 * This is the model class for table "web.crontabs".
 * @property string $runTime Шаблон времени запуска
 * @property string $jobClass Класс объект которого надо создать для выполнения задания
 * @property string $descript Описнаие задания
 * @property DateTime $lastRunStart Время начала последнего выполнения
 * @property DateTime $lastRunStop Время окончания последнего выполнения задания
 */
class Crontabs extends ActiveRecord
{
    const SC_EDIT = 'edit';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crontabs}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => DateTimeBehavior::class,
                'attributes' => ['lastRunStart', 'lastRunStop'],
                'format' => DateTimeBehavior::FORMAT_DATETIME,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['jobClass', 'descript'], 'required'],
            [['jobClass', 'descript'], 'string', 'max' => 255],
            [['jobClass'], 'in', 'range' => self::availableCronjobs()],
            [['runTime'], 'string', 'max' => 40],
            [['descript', 'runTime'], HtmlFilter::class],

        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => ['jobClass', 'runTime'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'runTime' => \Yii::t('yiicms', 'Шаблон времени запуска'),
            'jobClass' => \Yii::t('yiicms', 'Класс задания'),
            'descript' => \Yii::t('yiicms', 'Описание задания'),
            'lastRunStart' => \Yii::t('yiicms', 'Время начала последнего выполнения'),
            'lastRunStop' => \Yii::t('yiicms', 'Время окончания последнего выполнения'),
        ];
    }

    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);
        if ($result && empty($this->descript) && $this->validate(['jobClass'])) {
            $cronJob = self::createJob($this->jobClass);
            $this->descript = $cronJob->description;
        }

        return $result;
    }

    /**
     * выполняет запуск всех заданий
     */
    public static function startAll()
    {
        $startTime = self::startTime();

        /** @var self[] $jobs */
        $jobs = self::find()->all();
        foreach ($jobs as $job) {
            if (self::test($startTime, $job->runTime)) {
                self::runJob($job);
            }
        }
    }

    /**
     * выполняет выполнение одного задания
     * @param Crontabs $job задание
     * @return bool
     */
    public static function runJob($job)
    {
        $cronJob = self::createJob($job->jobClass);

        $job->lastRunStart = new DateTime();
        if (!$cronJob->run()) {
            return false;
        }

        $job->lastRunStop = new DateTime();
        $job->save();
        return true;
    }

    /**
     * создает объект работы
     * @param string $jobClass класс работы
     * @return CronJob
     */
    public static function createJob($jobClass)
    {
        $refl = new \ReflectionClass($jobClass);
        return $refl->newInstanceArgs();
    }

    /**
     * выдает массив getdate
     * @return array
     */
    public static function startTime()
    {
        $result = getdate();
        if ($result['wday'] === 0) {
            $result['wday'] = 7;
        }
        return $result;
    }

    /**
     * выдает key-value массив доступных работ где key класс а value описание
     * @return array
     */
    public static function getJobClassDropDown()
    {
        $nameJobs = self::availableCronjobs();

        $result = [];
        foreach ($nameJobs as $jobClass) {
            $cronJob = self::createJob($jobClass);
            $result[$jobClass] = $cronJob->description;
        }

        return $result;
    }

    /**
     * список доступных классов заданий планирощика
     * @return string[]
     */
    public static function availableCronjobs()
    {
        /** @var CommonApplicationTrait $app */
        $app = \Yii::$app;
        $namespaces = ArrayHelper::asArray($app->cronjobsNamespaces);
        $result = [];
        foreach ($namespaces as $namespace) {
            $path = \Yii::getAlias(str_replace('\\', '/', "@$namespace"));

            $files = scandir($path);
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
     * проверяет запускать или нет задание
     * @param array $startTime время старта
     * @param string $runTemplate шаблон времени старта
     * @return bool|int -1 в случае если задание не выполнялось, true если выполнение успешно
     */
    public static function test($startTime, $runTemplate)
    {
        if (empty($runTemplate)) {
            return false;
        }
        $tm = explode(' ', $runTemplate);
        return (is_array($tm) && count($tm) === 5 &&
            self::testMinutes($tm[0], $startTime) &&
            self::testHours($tm[1], $startTime) &&
            self::testDays($tm[2], $startTime) &&
            self::testMonths($tm[3], $startTime) &&
            self::testWeekDays($tm[4], $startTime));
    }

    private static $ret;

    /**
     * проверяет соответствует ли шаблон дня текущему времени
     * @param string $day шаблон
     * @param array $startTime время старта
     * @return bool
     */
    private static function testDays($day, $startTime)
    {
        self::$ret = [];
        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) {
                if ($match[0] === '*') {
                    self::$ret = range(1, 31);
                } elseif (is_numeric($match[0])) {
                    self::$ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)mb_substr($match[0], 2, 3);
                    $i = $period;
                    while ($i < 31) {
                        self::$ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    self::$ret = range($k[0], $k[1]);
                }
                return '';
            },
            $day
        );
        return in_array($startTime['mday'], self::$ret, true);
    }

    /**
     * проверяет соответствует ли шаблон месяца текущему времени
     * @param string $month шаблон
     * @param array $startTime время старта
     * @return bool
     */
    private static function testMonths($month, $startTime)
    {
        self::$ret = [];
        // если задано словами
        $month = str_ireplace(
            ['dec', 'nov', 'okt', 'sep', 'aug', 'jul', 'jun', 'may', 'apr', 'mar', 'feb', 'jan'],
            [12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1],
            $month
        );

        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) {
                if ($match[0] === '*') {
                    self::$ret = range(1, 12);
                } elseif (is_numeric($match[0])) {
                    self::$ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)substr($match[0], 2, 3);
                    $i = $period;
                    while ($i <= 12) {
                        self::$ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    self::$ret = range($k[0], $k[1]);
                }
            },
            $month
        );
        return in_array($startTime['mon'], self::$ret, true);
    }

    /**
     * проверяет соответствует ли шаблон дня недели текущему времени
     * @param string $weekDay шаблон
     * @param array $startTime время старта
     * @return bool
     */
    private static function testWeekDays($weekDay, $startTime)
    {
        self::$ret = [];
        //если задано словами
        $weekDay = str_ireplace(
            ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
            [7, 1, 2, 3, 4, 5, 6],
            $weekDay
        );
        $weekDay = str_ireplace('0', '7', $weekDay);

        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) {
                if ($match[0] === '*') {
                    self::$ret = range(1, 7);
                } elseif (is_numeric($match[0])) {
                    self::$ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)substr($match[0], 2, 3);
                    $i = $period;
                    while ($i <= 7) {
                        self::$ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    self::$ret = range($k[0], $k[1]);
                }
            },
            $weekDay
        );

        return in_array($startTime['wday'], self::$ret, true);
    }

    /**
     * проверяет соответствует ли шаблон часов текущему времени
     * @param string $hour шаблон
     * @param array $startTime время старта
     * @return bool
     */
    private static function testHours($hour, $startTime)
    {
        self::$ret = [];

        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) {
                if ($match[0] === '*') {
                    self::$ret = range(0, 23);
                } elseif (is_numeric($match[0])) {
                    self::$ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)substr($match[0], 2, 3);
                    $i = 0;
                    while ($i <= 23) {
                        self::$ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    self::$ret = range($k[0], $k[1]);
                }
            },
            $hour
        );

        return in_array($startTime['hours'], self::$ret, true);
    }

    /**
     * проверяет соответствует ли шаблон минут текущему времени
     * @param string $minute
     * @param array $startTime время старта
     * @return bool
     */
    private static function testMinutes($minute, $startTime)
    {
        //если */число то для запуска текущая минута должна быть кратна числу в знаменателе
        //если * то задание должно быть запущено в текущую минуту
        //если число1, число2,.. , числоN то проверяем равна ли текущая минута указной
        //если число то сравниваем это число с текущей минутой
        //если число-число то определяем попадает ли текущая минута в этот диапазон
        self::$ret = [];

        preg_replace_callback(
            '/(\*\/\d+|^\*$|^\d+$|\d+\-\d+|\d+)/',
            function ($match) {
                if ($match[0] === '*') {
                    self::$ret = range(0, 59);
                } elseif (is_numeric($match[0])) {
                    self::$ret[] = (int)$match[0];
                } elseif (preg_match('/^\*\/\d+$/', $match[0])) {
                    $period = (int)substr($match[0], 2, 3);
                    $i = 0;
                    while ($i <= 59) {
                        self::$ret[] = $i;
                        $i += $period;
                    }
                } elseif (preg_match('/^\d+\-\d+$/', $match[0])) {
                    $k = explode('-', $match[0]);
                    self::$ret = range($k[0], $k[1]);
                }
            },
            $minute
        );

        return in_array($startTime['minutes'], self::$ret, true);
    }
}
