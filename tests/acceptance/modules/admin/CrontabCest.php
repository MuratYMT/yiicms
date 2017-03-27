<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.06.2016
 * Time: 16:24
 */

namespace tests\acceptance\modules\admin;

use Facebook\WebDriver\WebDriverKeys;
use tests\acceptance\AcceptanceCest;
use yiicms\tests\_data\fixtures\models\core\CrontabFixture;

class CrontabCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            CrontabFixture::className(),
        ];
    }

    public function testAdd(\AcceptanceTester $I)
    {
        self::_openCrontab($I);
        $I->see('Планировщик заданий', 'h1');

        self::_addJob($I, 'Отправка электронной почты адресатам');

        $I->see('Задание "Отправка электронной почты адресатам" добавлено', '.toast-message');

        $I->see('Отправка электронной почты адресатам', 'table>tbody>tr>td');
        $I->see('* * * * *', 'table>tbody>tr>td');
    }

    public function testEdit(\AcceptanceTester $I)
    {
        self::_openCrontab($I);
        self::_addJob($I, 'Отправка электронной почты адресатам');
        self::_selectCrontab($I, 'Отправка электронной');
        $I->see('Отправка электронной почты адресатам', 'table>tbody>tr>td');

        $I->clickPopupMenu('Изменить');
        $I->see('Изменить задание "Отправка электронной почты адресатам"', 'h1');

        $I->seeOptionIsSelected('select[name="Crontabs[jobClass]"]', 'Отправка электронной почты адресатам');
        $I->seeInField('input[name="Crontabs[runTime]"]', '* * * * *');

        $I->fillField('input[name="Crontabs[runTime]"]', '* * * * */10');
        $I->click('Сохранить');

        $I->see('Задание "Отправка электронной почты адресатам" изменено', '.toast-message');

        $I->see('Отправка электронной почты адресатам', 'table>tbody>tr>td');
        $I->see('* * * * */10', 'table>tbody>tr>td');
    }

    public function testRun(\AcceptanceTester $I)
    {
        self::_openCrontab($I);
        self::_addJob($I, 'Отправка электронной почты адресатам');
        self::_selectCrontab($I, 'Отправка электронной');
        $I->clickPopupMenu('Запустить', null, null, 1);

        $I->see('Запустить?');
        $I->clickDlgConfirm();
        $I->waitForJS('return $.active == 0;', 60);
        $I->see('Задание "Отправка электронной почты адресатам" было запущено', '.toast-message');
    }

    public function testDel(\AcceptanceTester $I)
    {
        self::_openCrontab($I);
        self::_addJob($I, 'Отправка электронной почты адресатам');
        self::_selectCrontab($I, 'Отправка электронной');
        $I->clickPopupMenu('Удалить', null, null, 1);

        $I->see('Удалить?');
        $I->clickDlgConfirm();
        $I->see('Задание "Отправка электронной почты адресатам" удалено', '.toast-message');
        $I->dontSee('Отправка электронной почты адресатам', 'table>tbody>tr>td');
    }

    // -----------------------------------------------------------

    public static function _selectCrontab(\AcceptanceTester $I, $crontab)
    {
        $I->fillField('input[name="CrontabsSearch[descript]"]', $crontab);
        $I->pressKey('input[name="CrontabsSearch[descript]"]', WebDriverKeys::ENTER);
    }

    public static function _openCrontab(\AcceptanceTester $I, $auth = true)
    {
        if ($auth) {
            self::login($I);
        }

        $I->amOnPage('/admin/crontab');
    }

    public static function _addJob(\AcceptanceTester $I, $jobTitle)
    {
        $I->click('Добавить задание');
        $I->see('Добавить задание', 'h1');

        $I->selectOption('select[name="Crontabs[jobClass]"]', $jobTitle);
        $I->fillField('input[name="Crontabs[runTime]"]', '* * * * *');
        $I->click('Сохранить');
    }
}
