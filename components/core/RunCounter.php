<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 17.02.2016
 * Time: 22:48
 */

namespace yiicms\components\core;

class RunCounter
{
    private $_startTime;

    public function __construct()
    {
        $this->_startTime = microtime(true);
    }

    /**
     * @return \Closure
     */
    public function getExcecutionTime()
    {
        return function () {
            if (\Yii::$app->request->isAjax) {
                return;
            }
            echo (microtime(true) - $this->_startTime) * 1000;
        };
    }
}
