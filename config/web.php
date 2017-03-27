<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26.06.2015
 * Time: 9:04
 */

use yii\web\DbSession;
use yii\web\Response;
use yii\web\View;
use yiicms\models\core\Users;

return [
    'components' => [
        'user' => [
            'on ' . yii\web\User::EVENT_AFTER_LOGIN => [Users::class, 'afterLogin'],
            'identityClass' => Users::class,
            'enableAutoLogin' => true,
            'loginUrl' => '/login',
        ],
        'request' => [
            'cookieValidationKey' => 'ksdfk4589%*sd7uhgjdkh*lknfg(dsjnmu3ka78',
        ],
        'response' => [
            'class' => Response::class,
        ],
        'session' => [
            'class' => DbSession::class,
            'sessionTable' => '{{%sessions}}',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'view' => [
            'class' => View::class,
        ],
    ],
];
