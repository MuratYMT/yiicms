<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 27.12.2016
 * Time: 9:56
 */

namespace yiicms\tests\unit\tests\components;

use yiicms\components\core\DateTime;
use yiicms\tests\unit\helpers\DateTimeValidatorModel;
use yiicms\tests\unit\helpers\DateTimeValidatorModelTrunc;
use tests\unit\UnitCest;

class DateTimeValidatorCest extends UnitCest
{
    public function testDate(\MyUnitTester $I)
    {
        $model = new DateTimeValidatorModel();
        $model->dt = '2016-12-17';
        $I->assertTrue($model->validate(['dt']));
        $I->assertInstanceOf(DateTime::class, $model->dt);
        $model->dt = '136';
        $I->assertTrue($model->validate(['dt']));
        $I->assertInstanceOf(DateTime::class, $model->dt);
        $model->dt = 'eeee';
        $I->assertFalse($model->validate(['dt']));
        $I->assertNotInstanceOf(DateTime::class, $model->dt);
        $model->dt = ['2016-12-17'];
        $I->assertFalse($model->validate(['dt']));
        $I->assertNotInstanceOf(DateTime::class, $model->dt);
        $model->dt = '2016-13-17';
        $I->assertFalse($model->validate(['dt']));
        $I->assertNotInstanceOf(DateTime::class, $model->dt);
        $model->dt = '2015-02-29';
        $I->assertTrue($model->validate(['dt']));
        $I->assertInstanceOf(DateTime::class, $model->dt);

        //с временем
        $model->dt = '2016-12-17 08:08:17';
        $I->assertTrue($model->validate(['dt']));
        $I->assertInstanceOf(DateTime::class, $model->dt);

        $model->dt = '36 08:08:17';
        $I->assertFalse($model->validate(['dt']));
        $I->assertNotInstanceOf(DateTime::class, $model->dt);

        $model->dt = 'fff 08:08:17';
        $I->assertFalse($model->validate(['dt']));
        $I->assertNotInstanceOf(DateTime::class, $model->dt);

        //округления до даты
        $model = new DateTimeValidatorModelTrunc();
        $model->dt = '2016-12-17';
        $I->assertTrue($model->validate(['dt']));
        $I->assertInstanceOf(DateTime::class, $model->dt);
        $I->assertEquals(new DateTime('2016-12-17', 'UTC'), $model->dt);
        $model->dt = '2016-12-17 08:08:17';
        $I->assertTrue($model->validate(['dt']));
        $I->assertInstanceOf(DateTime::class, $model->dt);
        $I->assertEquals(new DateTime('2016-12-17', 'UTC'), $model->dt);
    }
}
