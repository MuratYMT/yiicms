<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26.06.2015
 * Time: 9:03
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../config/bootstrap.php';
$timer = new \yiicms\components\core\RunCounter();

$config = require __DIR__ . '/../config/common.php';

$config = yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/../config/web.php');

$config = \yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/../config/local/local.php');

$application = new \yiicms\components\core\yii\WebApplication($config);
$response = $application->response;
$application->run();
