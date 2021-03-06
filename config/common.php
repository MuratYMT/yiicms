<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 16.02.2017
 * Time: 11:54
 */

use yii\caching\FileCache;
use yii\console\controllers\MigrateController;
use yii\i18n\PhpMessageSource;
use yii\log\DbTarget;
use yii\rbac\DbManager;
use yii\swiftmailer\Mailer;
use yiicms\models\core\Log;
use yiicms\models\core\Settings;

$config = [
    'id' => 'yiicms',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'runtimePath' => dirname(__DIR__) . '/runtime',
    'controllerNamespace' => 'yiicms\controllers',
    'settingsNamespaces' => ['yiicms\\settings'],
    'blocksNamespaces' => ['yiicms\\blocks'],
    'cronjobsNamespaces' => ['yiicms\\cronjobs'],
    'themesNamespaces' => ['yiicms\\themes'],
    'adminMenuNamespaces' => 'yiicms\\config\\adminmenu',
    'modules' => require __DIR__ . '/include/modules.php',
    'controllerMap' => [
        'migrate' => [
            'class' => MigrateController::class,
            'migrationPath' => null,
            'migrationNamespaces' => [
                'yiicms\migrations',
            ],
        ],
    ],
    'timeZone' => 'UTC',
    'name' => 'Yii2 CMS',
    'bootstrap' => ['log'],
    'language' => 'ru',
    'components' => [
        'urlManager' => require __DIR__ . '/include/urlmanager.php',
        'log' => [
            'traceLevel' => YII_DEBUG ? 10 : 0,
            'targets' => [
                'db' => [
                    'class' => DbTarget::class,
                    'levels' => ['error', 'warning'],
                    'logTable' => Log::tableName(),
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => PhpMessageSource::class,
                    //'basePath' => '@app/messages',
                    //'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
                'yiicms' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'ru',
                ],
                'modules/*' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'cache' => [
            'class' => FileCache::class,
            'keyPrefix' => (isset($_SERVER['HTTP_HOST']) && !YII_ENV_TEST) ? $_SERVER['HTTP_HOST'] : 'local',
        ],
        'mailer' => [
            'class' => Mailer::class,
            //'viewPath' => '@theme/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'authManager' => [
            'class' => DbManager::class,
            'cache' => 'cache',
            'itemTable' => '{{%rbacItem}}',
            'itemChildTable' => '{{%rbacItemChild}}',
            'assignmentTable' => '{{%rbacAssignment}}',
            'ruleTable' => '{{%rbacRule}}',
        ],
        'settings' => [
            'class' => Settings::class,
        ],
        'db' => [
            'class' => \yiicms\components\core\db\Connection::class,
            'dsn' => 'mysql:host=localhost;dbname=yiicms',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            //'attributes' => [PDO::ATTR_PERSISTENT => true]
        ],
        'blockService' => [
            'class' => \yiicms\services\BlockService::class,
        ]
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['192.168.56.1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.56.1'],
    ];
}

return $config;