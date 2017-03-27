<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 08.12.2016
 * Time: 10:24
 */

namespace yiicms\tests\unit\tests\components;

use yiicms\components\core\DateTime;
use tests\unit\UnitCest;

class DateTimeCest extends UnitCest
{
    public function testDaysBetweenDates(\MyUnitTester $I)
    {
        $fromDate = new DateTime('2016-11-01');
        $toDate = new DateTime('2016-11-01');

        $I->assertEquals(0, DateTime::daysBetweenDates($fromDate, $toDate));
        $I->assertEquals(1, DateTime::daysBetweenDates($fromDate, $toDate, true));
        $I->assertEquals(0, DateTime::daysBetweenDates($toDate, $fromDate));
        $I->assertEquals(1, DateTime::daysBetweenDates($toDate, $fromDate, true));

        $toDate = new DateTime('2016-12-01');

        $I->assertEquals(30, DateTime::daysBetweenDates($fromDate, $toDate));
        $I->assertEquals(31, DateTime::daysBetweenDates($fromDate, $toDate, true));
        $I->assertEquals(-30, DateTime::daysBetweenDates($toDate, $fromDate));
        $I->assertEquals(-31, DateTime::daysBetweenDates($toDate, $fromDate, true));

        $toDate = new DateTime('2017-01-05');

        $I->assertEquals(65, DateTime::daysBetweenDates($fromDate, $toDate));
        $I->assertEquals(66, DateTime::daysBetweenDates($fromDate, $toDate, true));
        $I->assertEquals(-65, DateTime::daysBetweenDates($toDate, $fromDate));
        $I->assertEquals(-66, DateTime::daysBetweenDates($toDate, $fromDate, true));
    }

    public function testMonthsBetweenDates(\MyUnitTester $I)
    {
        $fromDate = new DateTime('2016-11-01');
        $toDate = new DateTime('2016-11-01');

        $I->assertEquals(0, round(DateTime::monthsBetweenDates($fromDate, $toDate), 6));
        $I->assertEquals(0.033333, round(DateTime::monthsBetweenDates($fromDate, $toDate, true), 6));
        $I->assertEquals(0, round(DateTime::monthsBetweenDates($toDate, $fromDate), 6));
        $I->assertEquals(0.033333, round(DateTime::monthsBetweenDates($toDate, $fromDate, true), 6));

        $toDate = new DateTime('2016-11-30');

        $I->assertEquals(0.966667, round(DateTime::monthsBetweenDates($fromDate, $toDate), 6));
        $I->assertEquals(1, round(DateTime::monthsBetweenDates($fromDate, $toDate, true), 6));
        $I->assertEquals(-0.966667, round(DateTime::monthsBetweenDates($toDate, $fromDate), 6));
        $I->assertEquals(-1, round(DateTime::monthsBetweenDates($toDate, $fromDate, true), 6));

        $toDate = new DateTime('2016-12-01');

        $I->assertEquals(1, DateTime::monthsBetweenDates($fromDate, $toDate));
        $I->assertEquals(1.032258, round(DateTime::monthsBetweenDates($fromDate, $toDate, true), 6));
        $I->assertEquals(-1, DateTime::monthsBetweenDates($toDate, $fromDate));
        $I->assertEquals(-1.032258, round(DateTime::monthsBetweenDates($toDate, $fromDate, true), 6));

        $toDate = new DateTime('2017-01-05');

        $I->assertEquals(2.129032, round(DateTime::monthsBetweenDates($fromDate, $toDate), 6));
        $I->assertEquals(2.161290, round(DateTime::monthsBetweenDates($fromDate, $toDate, true), 6));
        $I->assertEquals(-2.129032, round(DateTime::monthsBetweenDates($toDate, $fromDate), 6));
        $I->assertEquals(-2.161290, round(DateTime::monthsBetweenDates($toDate, $fromDate, true), 6));
    }
}
