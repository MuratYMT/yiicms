<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.11.2016
 * Time: 8:01
 */

namespace tests\acceptance\modules\admin;

use tests\acceptance\AcceptanceCest;
use yiicms\models\core\BlocksForRole;
use yiicms\tests\_data\fixtures\models\core\BlocksFixture;
use yiicms\tests\_data\fixtures\models\core\MenusFixture;

class BlocksCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            BlocksFixture::className(),
            MenusFixture::className(),
        ];
    }

    public function testIndex(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);
    }

    public function addBlock(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);
        $I->click('Создать', null, 1);
        $I->click('Блок меню');
        $I->see('Создать блок', 'h1');

        $I->seeInField('input[name="WidgetEditor[ru__title]"]', '');
        $I->seeInField('input[name="WidgetEditor[description]"]', '');
        $I->seeOptionIsSelected('select[name="WidgetEditor[position]"]', 'topNavigation');
        $I->seeOptionIsSelected('select[name="WidgetEditor[viewFile]"]', 'index.php');
        $I->seeInField('input[ name="WidgetEditor[weight]"]', 0);
        //$I->seeCheckboxIsChecked('input[name="WidgetEditor[activy]"]');
        $I->seeOptionIsSelected('select[name="WidgetEditor[pathInfoVisibleOrder]"]', 'Не учитывать');

        self::_addBlock($I);

        $I->see('Блок "Блок меню" создан', '.toast-message');

        $I->see('Блок меню', 'table>tbody>tr>td');
        $I->see('Тестовое меню', 'table>tbody>tr>td');
    }

    public function editBlock(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);

        $I->click('Создать', null, 1);
        $I->click('Блок меню');

        self::_addBlock($I);

        $I->clickPopupMenu('Изменить', 'blocks-grid');
        $I->see('Изменить блок "Блок меню"', 'h1');

        $I->seeInField('input[name="WidgetEditor[ru__title]"]', 'Блок меню');
        $I->seeInField('input[name="WidgetEditor[description]"]', 'Тестовое меню');
        $I->seeOptionIsSelected('select[name="WidgetEditor[position]"]', 'topNavigation');
        $I->seeOptionIsSelected('select[name="WidgetEditor[viewFile]"]', 'index.php');
        $I->seeInField('input[ name="WidgetEditor[weight]"]', 0);
        //$I->seeCheckboxIsChecked('input[name="WidgetEditor[activy]"]');
        $I->seeOptionIsSelected('select[name="WidgetEditor[pathInfoVisibleOrder]"]', 'Не учитывать');
        $I->seeOptionIsSelected('select[name="WidgetEditor[rootMenuId]"]', 'menu22');

        $I->fillField('input[name="WidgetEditor[ru__title]"]', 'Индекс yiicms');

        $I->click('Сохранить');

        $I->see('Блок "Индекс yiicms" отредактирован', '.toast-message');
        $I->see('Индекс yiicms', 'table>tbody>tr>td');
    }

    public function delBlock(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);

        $I->click('Создать', null, 1);
        $I->click('Блок меню');

        self::_addBlock($I);
        $I->clickPopupMenu('Удалить', 'blocks-grid', null, 1);
        $I->see('Удалить?');
        $I->clickDlgConfirm();

        $I->see('Блок "Блок меню" удален', '.toast-message');
        $I->dontSee('Блок меню', 'table>tbody>tr>td');
    }

    public function testAssignVisibleToRole(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);
        $I->click('Создать', null, 1);
        $I->click('Блок меню');
        self::_addBlock($I);

        $I->dontSeeRecord(BlocksForRole::className(), ['roleName'=>'role3']);

        self::_openBlocksPage($I, false);
        self::_assignToRole($I, 'role3');

        $I->seeRecord(BlocksForRole::className(), ['roleName' => 'role3']);
    }

    public function testRevokeVisibleToRole(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);
        $I->click('Создать', null, 1);
        $I->click('Блок меню');
        self::_addBlock($I);
        self::_assignToRole($I, 'role3');

        $I->seeRecord(BlocksForRole::className(), ['roleName' => 'role3']);

        self::_openBlocksPage($I, false);

        $I->clickPopupMenu('Видимость для ролей', 'blocks-grid');

        self::_selectRole($I, 'role3');
        $I->clickPopupMenu('Отменить', 'role-permission-grid');
        $I->see('Видимость блока для роли "role3" отменена');

        $I->dontSeeRecord(BlocksForRole::className(), ['roleName' => 'role3']);
    }

    public function testVisibleForPathAdd(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);
        $I->click('Создать', null, 1);
        $I->click('Блок меню');
        self::_addBlock($I);

        self::_addVisibleForPath($I, 'start');

        $I->see('Правило добавлено');

        $I->see('start', 'table>tbody>tr>td');
        $I->see('Содержит', 'table>tbody>tr>td');
    }

    public function testVisibleForPathEdit(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);
        $I->click('Создать', null, 1);
        $I->click('Блок меню');
        self::_addBlock($I);

        self::_addVisibleForPath($I, 'start');

        self::_openBlocksPage($I, false);
        $I->clickPopupMenu('Видимость на страницах', 'blocks-grid');
        $I->clickPopupMenu('Изменить');

        $I->see('Изменить правило видимости блока "Блок меню" на страницах сайта', 'h1');

        $I->seeInField('input[name="BlocksVisibleForPathInfo[template]"]', 'start');
        $I->seeOptionIsSelected('select[name="BlocksVisibleForPathInfo[rule]"]', 'Содержит');

        $I->fillField('input[name="BlocksVisibleForPathInfo[template]"]', 'start2');
        $I->selectOption('select[name="BlocksVisibleForPathInfo[rule]"]', 'Равно');

        $I->click('Сохранить');
        $I->see('Правило изменено');

        $I->see('start2', 'table>tbody>tr>td');
        $I->see('Равно', 'table>tbody>tr>td');
    }

    public function testVisibleForPathDel(\AcceptanceTester $I)
    {
        self::_openBlocksPage($I);
        $I->click('Создать', null, 1);
        $I->click('Блок меню');
        self::_addBlock($I);

        self::_addVisibleForPath($I, 'start');

        self::_openBlocksPage($I, false);

        $I->clickPopupMenu('Видимость на страницах', 'blocks-grid');
        $I->clickPopupMenu('Удалить');
        $I->see('Удалить это правило?');

        $I->clickDlgConfirm();
        $I->see('Правило видимости для блока "Блок меню" удалено');

        $I->dontSee('start', 'table>tbody>tr>td');
        $I->dontSee('Равно', 'table>tbody>tr>td');
    }

    // -----------------------------------------------------------------------------------------------------------------------------------------------

    public static function _addVisibleForPath(\AcceptanceTester $I, $path)
    {
        $I->clickPopupMenu('Видимость на страницах', 'blocks-grid');
        $I->see('Правила видимости блока "Блок меню" на страницах сайта', 'h1');

        $I->click('Создать правило');
        $I->see('Добавить правило видимости блока "Блок меню" на страницах сайта', 'h1');

        $I->fillField('input[name="BlocksVisibleForPathInfo[template]"]', $path);
        $I->click('Сохранить');
    }

    public static function _addBlock(\AcceptanceTester $I)
    {
        $I->fillField('input[name="WidgetEditor[ru__title]"]', 'Блок меню');
        $I->fillField('input[name="WidgetEditor[description]"]', 'Тестовое меню');
        $I->selectOption('select[name="WidgetEditor[position]"]', 'topNavigation');
        $I->selectOption('select[name="WidgetEditor[rootMenuId]"]', 'menu22');
        $I->click('Сохранить');
    }

    public static function _openBlocksPage(\AcceptanceTester $I, $auth = true)
    {
        if ($auth) {
            self::login($I);
        }
        $I->amOnPage('/admin/blocks');

        $I->see('Блоки', 'h1');
        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
    }

    public static function _selectRole(\AcceptanceTester $I, $role)
    {
        $I->fillField('input[name="BlocksVisibleForRoleSearch[roleName]"]', $role);
        $I->pressKey('input[name="BlocksVisibleForRoleSearch[roleName]"]', \WebDriverKeys::ENTER);
    }

    public static function _assignToRole(\AcceptanceTester $I, $role)
    {
        $I->clickPopupMenu('Видимость для ролей', 'blocks-grid');

        self::_selectRole($I, $role);
        $I->see($role, ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        $I->clickPopupMenu('Разрешить', 'role-permission-grid-container');
        $I->see("Видимость блока для роли \"$role\" предоставлена");
    }
}
