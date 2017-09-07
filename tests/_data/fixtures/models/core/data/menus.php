<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.06.2016
 * Time: 12:37
 */

use yiicms\components\core\MultiLangHelper;
use yiicms\models\core\constants\VisibleForPathInfoConst;
use yiicms\models\core\VisibleForPathInfo;

return [
    'm1' => [
        'menuId' => 100,
        'parentId' => 0,
        'mPath' => '100',
        'link' => 'l1',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu1', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm2' => [
        'menuId' => 200,
        'parentId' => 0,
        'mPath' => '200',
        'link' => 'l2',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu2', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm21' => [
        'menuId' => 210,
        'parentId' => 200,
        'mPath' => '200^210',
        'link' => 'l21',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu21', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm22' => [
        'menuId' => 220,
        'parentId' => 200,
        'mPath' => '200^220',
        'link' => 'l22',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu22', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm221' => [
        'menuId' => 221,
        'parentId' => 220,
        'mPath' => '200^220^221',
        'link' => 'l221',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu221', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm222' => [
        'menuId' => 222,
        'parentId' => 220,
        'mPath' => '200^220^222',
        'link' => 'l222',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu222', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm223' => [
        'menuId' => 223,
        'parentId' => 220,
        'mPath' => '200^220^223',
        'link' => 'l223',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu223', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm2231' => [
        'menuId' => 2231,
        'parentId' => 223,
        'mPath' => '200^220^223^2231',
        'link' => 'l2231',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu2231', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm23' => [
        'menuId' => 230,
        'parentId' => 200,
        'mPath' => '200^230',
        'link' => 'l23',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu23', 'ru'),
        'subTitleM' => json_encode([])
    ],
    'm3' => [
        'menuId' => 300,
        'parentId' => 0,
        'mPath' => '300',
        'link' => 'l3',
        'weight' => 0,
        'pathInfoVisibleOrder' => VisibleForPathInfoConst::VISIBLE_IGNORE,
        'titleM' => MultiLangHelper::setValue([], 'menu3', 'ru'),
        'subTitleM' => json_encode([])
    ]
];
