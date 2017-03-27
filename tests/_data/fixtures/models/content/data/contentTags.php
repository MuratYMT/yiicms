<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 26.05.2016
 * Time: 15:51
 */

use yii\helpers\Inflector;

return [
    't1' => [
        'tagId' => 100,
        'title' => 'Тег 1',
        'pageCount' => 3,
        'slug' => Inflector::slug('Тег 1'),
    ],
    't2' => [
        'tagId' => 200,
        'title' => 'Тег 2',
        'pageCount' => 1,
        'slug' => Inflector::slug('Тег 2'),
    ],
    't3' => [
        'tagId' => 300,
        'title' => 'Тег 3',
        'pageCount' => 0,
        'slug' => Inflector::slug('Тег 3'),
    ],
];
