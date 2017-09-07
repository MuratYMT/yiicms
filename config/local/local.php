<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.09.2017
 * Time: 18:34
 */

return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=192.168.56.201;dbname=yiicms',
            'username' => 'root',
            'password' => 'root',
            'schemaCacheDuration' => 300,
            'attributes' => [PDO::ATTR_PERSISTENT => false]
        ],
    ],
];