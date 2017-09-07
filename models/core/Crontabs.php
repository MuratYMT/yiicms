<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yiicms\components\core\behavior\DateTimeBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\YiiCms;

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
            [['jobClass'], 'in', 'range' => YiiCms::$app->crontabService->availableCronjobs()],
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
            $cronJob = YiiCms::$app->crontabService->createJob($this->jobClass);
            $this->descript = $cronJob->description;
        }

        return $result;
    }
}
