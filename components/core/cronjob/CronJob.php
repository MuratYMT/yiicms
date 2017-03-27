<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.01.2016
 * Time: 8:44
 */

namespace yiicms\components\core\cronjob;

use yii\base\Object;

abstract class CronJob extends Object
{
    /**
     * @var string описание задания
     */
    public $description = '';

    /**
     * функция входа в выполнение работы
     * @return bool true если задание выполнено успешно
     */
    abstract public function run();
}
