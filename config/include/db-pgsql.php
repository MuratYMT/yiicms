<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26.06.2015
 * Time: 9:10
 */

use yii\db\pgsql\Schema;
use yiicms\components\core\db\Connection;

return [
    'class' => Connection::class,
    'dsn' => 'pgsql:host=localhost;dbname=yiicms',
    'username' => 'postgres',
    'password' => 'postgres',
    'charset' => 'utf8',
    'enableSchemaCache' => true,
    /*'schemaMap' => [
        'pgsql' => [
            'class' => Schema::class,
            'defaultSchema' => 'web'
        ],
    ],
    'on afterOpen' => function ($event) {
        $event->sender->createCommand("SET search_path TO web")->execute();
    }*/
];