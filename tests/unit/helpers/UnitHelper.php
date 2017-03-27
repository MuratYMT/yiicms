<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 21.12.2016
 * Time: 11:27
 */

namespace yiicms\tests\unit\helpers;

use yii\base\Model;

trait UnitHelper
{
    private static $additionalEmpty = [0, [0], ['0'], [''], [null]];
    private static $emptyDefault = [null, '', []];

    /**
     * для проверки комбобоксов которые могут приянть только integer not null значение
     * @param \MyUnitTester $I
     * @param Model $model
     * @param string $attribute
     */
    protected static function testIntegerNotEmptyNotArray(\MyUnitTester $I, $model, $attribute)
    {
        foreach (array_merge(self::$additionalEmpty, self::$emptyDefault) as $value) {
            $model->$attribute = $value;
            $I->assertFalse($model->validate([$attribute]));
        }
    }

    /**
     * для проверки комбобоксов которые могут приянть integer null значение
     * @param \MyUnitTester $I
     * @param Model $model
     * @param string $attribute
     */
    protected static function testIntegerEmptyNotArray(\MyUnitTester $I, $model, $attribute)
    {
        foreach (self::$emptyDefault as $value) {
            $model->$attribute = $value;
            $I->assertTrue($model->validate([$attribute]));
            $I->assertNull($model->$attribute);
        }

        foreach (self::$additionalEmpty as $value) {
            $model->$attribute = $value;
            $I->assertFalse($model->validate([$attribute]));
        }
    }

    /**
     * для проверки строк которые могут приянть только string not null значение
     * @param \MyUnitTester $I
     * @param Model $model
     * @param string $attribute
     */
    protected static function testStringNotEmptyNotArray(\MyUnitTester $I, $model, $attribute)
    {
        foreach (self::$emptyDefault as $value) {
            $model->$attribute = $value;
            $I->assertFalse($model->validate([$attribute]));
        }
    }

    /**
     * для проверки строк которые могут приянть string null значение
     * @param \MyUnitTester $I
     * @param Model $model
     * @param string $attribute
     */
    protected static function testStringEmptyNotArray(\MyUnitTester $I, $model, $attribute)
    {
        foreach (self::$emptyDefault as $value) {
            $model->$attribute = $value;
            $I->assertTrue($model->validate([$attribute]));
            $I->assertNull($model->$attribute);
        }
    }
}
