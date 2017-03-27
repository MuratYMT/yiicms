<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 31.05.2016
 * Time: 11:40
 */

namespace common\unit\test\models\web;

use yiicms\models\core\Crontabs;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;
use tests\unit\UnitCest;

class CrontabsCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            UsersFixture::className(),
        ];
    }

    public function testMinutes(\MyUnitTester $I)
    {
        $startTime = [
            'seconds' => 40,
            'minutes' => 58,
            'hours' => 21,
            'mday' => 17,
            'wday' => 2,
            'mon' => 6,
            'year' => 2003,
            'yday' => 167,
            'weekday' => 'Tuesday',
            'month' => 'June',
            '0' => 1055901520
        ];

        $template = '* * * * *';

        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 0;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 59;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '35 * * * *';

        $startTime['minutes'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 59;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 35;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '*/3 * * * *';
        $startTime['minutes'] = 0;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 8;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 12;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 22;
        $I->assertFalse(Crontabs::test($startTime, $template));

        $template = '3-5 * * * *';
        $startTime['minutes'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 2;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 6;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 4;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['minutes'] = 5;
        $I->assertTrue(Crontabs::test($startTime, $template));
    }

    public function testHours(\MyUnitTester $I)
    {
        $startTime = [
            'seconds' => 40,
            'minutes' => 58,
            'hours' => 21,
            'mday' => 17,
            'wday' => 2,
            'mon' => 6,
            'year' => 2003,
            'yday' => 167,
            'weekday' => 'Tuesday',
            'month' => 'June',
            '0' => 1055901520
        ];

        $template = '* * * * *';

        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['hours'] = 0;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['hours'] = 23;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* 22 * * *';

        $startTime['hours'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['hours'] = 9;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['hours'] = 22;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* */3 * * *';
        $startTime['hours'] = 0;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['hours'] = 8;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['hours'] = 12;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['hours'] = 22;
        $I->assertFalse(Crontabs::test($startTime, $template));

        $template = '* 3-5 * * *';
        $startTime['hours'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['hours'] = 2;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['hours'] = 6;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['hours'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['hours'] = 4;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['hours'] = 5;
        $I->assertTrue(Crontabs::test($startTime, $template));
    }

    public function testMonthDay(\MyUnitTester $I)
    {
        $startTime = [
            'seconds' => 40,
            'minutes' => 58,
            'hours' => 21,
            'mday' => 17,
            'wday' => 2,
            'mon' => 6,
            'year' => 2003,
            'yday' => 167,
            'weekday' => 'Tuesday',
            'month' => 'June',
            '0' => 1055901520
        ];

        $template = '* * * * *';

        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mday'] = 1;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mday'] = 31;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * 22 * *';

        $startTime['mday'] = 1;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mday'] = 9;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mday'] = 22;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * */3 * *';
        $startTime['mday'] = 1;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mday'] = 8;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mday'] = 12;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mday'] = 22;
        $I->assertFalse(Crontabs::test($startTime, $template));

        $template = '* * 3-5 * *';
        $startTime['mday'] = 1;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mday'] = 2;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mday'] = 6;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mday'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mday'] = 4;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mday'] = 5;
        $I->assertTrue(Crontabs::test($startTime, $template));
    }

    public function testWeekDay(\MyUnitTester $I)
    {
        $startTime = [
            'seconds' => 40,
            'minutes' => 58,
            'hours' => 21,
            'mday' => 17,
            'wday' => 2,
            'mon' => 6,
            'year' => 2003,
            'yday' => 167,
            'weekday' => 'Tuesday',
            'month' => 'June',
            '0' => 1055901520
        ];

        $template = '* * * * *';

        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['wday'] = 1;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['wday'] = 7;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * * 3';

        $startTime['wday'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 9;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * * wed';

        $startTime['wday'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 9;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * * ThU';

        $startTime['wday'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 9;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 4;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * * */3';
        $startTime['wday'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 8;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 7;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * * 3-5';
        $startTime['wday'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 2;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 6;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['wday'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['wday'] = 4;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['wday'] = 5;
        $I->assertTrue(Crontabs::test($startTime, $template));
    }

    public function testMonth(\MyUnitTester $I)
    {
        $startTime = [
            'seconds' => 40,
            'minutes' => 58,
            'hours' => 21,
            'mday' => 17,
            'wday' => 2,
            'mon' => 6,
            'year' => 2003,
            'yday' => 167,
            'weekday' => 'Tuesday',
            'month' => 'June',
            '0' => 1055901520
        ];

        $template = '* * * * *';

        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mon'] = 1;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mon'] = 12;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * 3 *';

        $startTime['mon'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 9;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * jan *';

        $startTime['mon'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 9;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 1;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * apR *';

        $startTime['mon'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 9;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 4;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * */3 *';
        $startTime['mon'] = 1;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 8;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 7;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));

        $template = '* * * 3-5 *';
        $startTime['mon'] = 0;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 2;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 6;
        $I->assertFalse(Crontabs::test($startTime, $template));
        $startTime['mon'] = 3;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mon'] = 4;
        $I->assertTrue(Crontabs::test($startTime, $template));
        $startTime['mon'] = 5;
        $I->assertTrue(Crontabs::test($startTime, $template));
    }
}
