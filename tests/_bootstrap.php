<?php
// This is global bootstrap for autoloading

$appDir = dirname(__DIR__);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', $appDir);

require $appDir . '/vendor/autoload.php';
require $appDir . '/vendor/yiisoft/yii2/Yii.php';
require $appDir . '/config/bootstrap.php';

\Yii::setAlias('@yiicms', $appDir);
\Yii::setAlias('@uploadFolder','@yiicms/upload');
