<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 17:03
 */

use codemix\localeurls\UrlManager;

return [
    'class' => UrlManager::class,
    'languages' => ['ru', 'en'],
    'enableDefaultLanguageUrlCode' => true,
    'enablePrettyUrl' => true,
    'enableStrictParsing' => false,
    'showScriptName' => false,
    'rules' => [
        '<action:(login|logout|registration|activation|reset-password|restore-password)>' => 'users/users/<action>',
        'filemanager' => 'filemanager/default/index',
        'filemanager/<action:[\w|-]+>' => 'filemanager/default/<action>',
        'profile' => 'users/profile/index',
        'profile/<action:[\w|-]+>' => 'users/profile/<action>',
        'pmails' => 'users/pmails/index',
        'pmails/<action:[\w|-]+>' => 'users/pmails/<action>',
        '<action:captcha>' => 'site/<action>',
        'page/<slug:[\w|\d|\-]+>' => 'content/pages',
        'category' => 'content/categories',
        'category/<slug:[\w|\d|\-]+>' => 'content/categories',
        'tag/<slug:[\w|\d|\-]+>' => 'content/tags',
    ],
];
