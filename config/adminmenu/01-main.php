<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.02.2017
 * Time: 8:38
 */

namespace yiicms\menu;

return [
    ['label' => \Yii::t('yiicms', 'Домой'), 'url' => ['/admin'], 'icon' => 'fa fa-home'],
    [
        'label' => \Yii::t('yiicms', 'Контент'),
        'url' => '#',
        'icon' => 'fa fa-edit',
        'items' => [
            ['label' => \Yii::t('yiicms', 'Категории'), 'url' => ['/admin/categories'], 'icon' => 'fa fa-bars'],
            ['label' => \Yii::t('yiicms', 'Страницы'), 'url' => ['/admin/pages'], 'icon' => 'fa fa-copy'],
        ],
    ],
    ['label' => \Yii::t('yiicms', 'Пользователи'), 'url' => ['/admin/users'], 'icon' => 'fa fa-user'],
    ['label' => \Yii::t('yiicms', 'Роли'), 'url' => ['/admin/roles'], 'icon' => 'fa fa-users'],
    ['label' => \Yii::t('yiicms', 'Блоки'), 'url' => ['/admin/blocks'], 'icon' => 'fa fa-square-o'],
    ['label' => \Yii::t('yiicms', 'Меню'), 'url' => ['/admin/menus'], 'icon' => 'fa fa-sitemap'],
    ['label' => \Yii::t('yiicms', 'Планировщик'), 'url' => ['/admin/crontab'], 'icon' => 'fa fa-clock-o'],
    ['label' => \Yii::t('yiicms', 'Настройки сайта'), 'url' => ['/admin/settings'], 'icon' => 'fa fa-gears'],
    ['label' => \Yii::t('yiicms', 'Лог ошибок'), 'url' => ['/admin/errors'], 'icon' => 'fa fa-file-o'],
    ['label' => \Yii::t('yiicms', 'Отправленная почта'), 'url' => ['/admin/mails'], 'icon' => 'fa fa-envelope-open-o'],
];
