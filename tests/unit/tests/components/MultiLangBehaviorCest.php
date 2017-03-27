<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.12.2016
 * Time: 11:05
 */

namespace yiicms\tests\unit\tests\components;

use yiicms\components\core\behavior\MultiLangBehavior2;

class MultiLangBehaviorCest
{
    public function testCompare(\MyUnitTester $I)
    {
        $lang1 = [
            'ru' => 'Казахстан',
            'en' => 'Kazakhstan',
        ];
        $I->assertTrue(MultiLangBehavior2::equal($lang1, $lang1));

        $lang2 = [
            'ru' => 'Казахстан2',
            'en' => 'Kazakhstan',
        ];
        $I->assertFalse(MultiLangBehavior2::equal($lang1, $lang2));

        $lang2 = [
            'ru' => 'Казахста',
            'en' => 'Kazakhstan',
        ];
        $I->assertFalse(MultiLangBehavior2::equal($lang1, $lang2));

        $lang2 = [
            'ru' => 'Казахстан',
        ];
        $I->assertFalse(MultiLangBehavior2::equal($lang1, $lang2));

        $lang2 = [
            'ru' => 'Казахстан',
            'en' => 'Kazakhstan',
            'kz' => 'Qazakhstan',
        ];
        $I->assertFalse(MultiLangBehavior2::equal($lang1, $lang2));
        $lang2 = [];
        $I->assertFalse(MultiLangBehavior2::equal($lang1, $lang2));

        $lang1 = [];
        $lang2 = [
            'ru' => 'Казахстан',
        ];
        $I->assertFalse(MultiLangBehavior2::equal($lang1, $lang2));
        $lang2 = [
            'ru' => 'Казахста',
            'en' => 'Kazakhstan',
        ];
        $I->assertFalse(MultiLangBehavior2::equal($lang1, $lang2));

        $lang1 = [
            'ru' => 'Казахстан',
            'en' => 'Kazakhstan',
        ];

        $lang2 = [
            'en' => 'Kazakhstan',
            'ru' => 'Казахстан',
        ];
        $I->assertTrue(MultiLangBehavior2::equal($lang1, $lang2));

        $lang1 = [
            'en' => 'Kazakhstan',
            'ru' => 'Казахстан',
        ];

        $lang2 = [
            'ru' => 'Kazakhstan',
            'en' => 'Казахстан',
        ];
        $I->assertFalse(MultiLangBehavior2::equal($lang1, $lang2));
    }
}
