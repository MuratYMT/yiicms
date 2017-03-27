<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 15:11
 */

use yiicms\models\content\CategoryPermission;

return [
    'p1' => [
        'categoryId' => 300,
        'roleName' => 'role2',
        'permission' => CategoryPermission::CATEGORY_VIEW,
    ],
    'p2' => [
        'categoryId' => 400,
        'roleName' => 'role2',
        'permission' => CategoryPermission::CATEGORY_VIEW,
    ],
    'p21' => [
        'categoryId' => 600,
        'roleName' => 'role2',
        'permission' => CategoryPermission::PAGE_CLOSE,
    ],
    'p3' => [
        'categoryId' => 500,
        'roleName' => 'role2',
        'permission' => CategoryPermission::CATEGORY_VIEW,
    ],
    'p5' => [
        'categoryId' => 600,
        'roleName' => 'role2',
        'permission' => CategoryPermission::COMMENT_ADD,
    ],
    'p4' => [
        'categoryId' => 700,
        'roleName' => 'role2',
        'permission' => CategoryPermission::CATEGORY_VIEW,
    ],
    'p6' => [
        'categoryId' => 700,
        'roleName' => 'role2',
        'permission' => CategoryPermission::COMMENT_ADD,
    ],
    'pp2' => [
        'categoryId' => 150,
        'roleName' => 'role1',
        'permission' => CategoryPermission::CATEGORY_VIEW,
    ],
    'pa1' => [
        'categoryId' => 150,
        'roleName' => 'role1',
        'permission' => CategoryPermission::PAGE_EDIT_OWN,
    ],
    'pa11' => [
        'categoryId' => 150,
        'roleName' => 'role1',
        'permission' => CategoryPermission::PAGE_ADD,
    ],
    'pa2' => [
        'categoryId' => 150,
        'roleName' => 'role2',
        'permission' => CategoryPermission::PAGE_EDIT,
    ],
    'pa3' => [
        'categoryId' => 300,
        'roleName' => 'role2',
        'permission' => CategoryPermission::PAGE_ADD,
    ],
    'pa31' => [
        'categoryId' => 300,
        'roleName' => 'role2',
        'permission' => CategoryPermission::PAGE_DELETE,
    ],
    'pa32' => [
        'categoryId' => 300,
        'roleName' => 'role2',
        'permission' => CategoryPermission::PAGE_EDIT,
    ],
    'pa4' => [
        'categoryId' => 800,
        'roleName' => 'role2',
        'permission' => CategoryPermission::PAGE_ADD,
    ],
];
