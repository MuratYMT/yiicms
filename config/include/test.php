<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.02.2017
 * Time: 14:58
 */

use yiicms\components\core\yii\WebApplication;

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common.php',
    require __DIR__ . '/web.php'
);

$config['class'] = WebApplication::class;
$config['components']['db']['dsn'] = require __DIR__ . '/test-db-mysql.php';

return $config;