<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 17:30
 */

use yiicms\modules\admin\AdminModule;
use yiicms\modules\content\ContentModule;
use yiicms\modules\filemanager\FilemanagerModule;
use kartik\grid\Module;
use yiicms\modules\users\UsersModule;

return [
    'gridview' => [
        'class' => Module::class,
    ],
    'users' => [
        'class' => UsersModule::class,
    ],
    'filemanager' => [
        'class' => FilemanagerModule::class,
    ],
    'admin' => [
        'class' => AdminModule::class,
    ],
    'content' => [
        'class' => ContentModule::class,
    ],
];