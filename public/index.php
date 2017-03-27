<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26.06.2015
 * Time: 9:03
 */

use yii\helpers\ArrayHelper;

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

$appDir = dirname(__DIR__);

require $appDir . '/vendor/autoload.php';
require $appDir . '/vendor/yiisoft/yii2/Yii.php';
require $appDir . '/config/bootstrap.php';

$config = ArrayHelper::merge(
    require $appDir . '/config/common.php',
    require $appDir . '/config/web.php'
);

if (YII_ENV_DEV && !YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => yii\debug\Module::className(),
        'allowedHosts' => ['main-host'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::className(),
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.56.1'],
    ];
}

$application = new yiicms\components\core\yii\WebApplication($config);
$application->run();
