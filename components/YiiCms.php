<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.09.2017
 * Time: 21:04
 */

namespace yiicms\components;

use yii\BaseYii;
use yiicms\components\core\yii\ConsoleApplication;
use yiicms\components\core\yii\WebApplication;

class YiiCms extends BaseYii
{
    /**
     * @var WebApplication|ConsoleApplication
     */
    public static $app;
}