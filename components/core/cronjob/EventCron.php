<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 08.12.2016
 * Time: 15:25
 */

namespace yiicms\components\core\cronjob;

use yiicms\components\core\DateTime;
use yii\base\Event;

class EventCron extends Event
{
    /** @var  DateTime время запуска */
    public $runTime;
    /**
     * @var bool результат выполнения события
     */
    public $result = true;
}
