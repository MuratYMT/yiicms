<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.06.2016
 * Time: 10:55
 */

namespace tests\acceptance\modules\admin;

use Facebook\WebDriver\WebDriverKeys;
use tests\acceptance\AcceptanceCest;
use yiicms\tests\_data\fixtures\models\core\MenusFixture;
use yiicms\tests\_data\fixtures\models\core\MenusForPathInfoFixture;
use yiicms\tests\_data\fixtures\models\core\MenusForRoleFixture;

class MenusCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            MenusFixture::className(),
            MenusForPathInfoFixture::className(),
            MenusForRoleFixture::className(),
        ];
    }

    public function testIndex(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);

        $I->see('menu1', 'table>tbody>tr>td');
        $I->see('menu2', 'table>tbody>tr>td');
        $I->see('menu21', 'table>tbody>tr>td');
        $I->see('menu22', 'table>tbody>tr>td');
        $I->see('menu221', 'table>tbody>tr>td');
        $I->see('menu222', 'table>tbody>tr>td');
        $I->see('menu223', 'table>tbody>tr>td');
        $I->see('menu2231', 'table>tbody>tr>td');
        $I->see('menu23', 'table>tbody>tr>td');
        $I->see('menu3', 'table>tbody>tr>td');
    }

    public function testAddRootMenu(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        $I->click('Создать корневой пункт меню');

        $I->see('Создать пункт меню', 'h1');

        $I->seeOptionIsSelected('select[name="Menus[parentId]"]', 'БЕЗ РОДИТЕЛЬСКОГО');
        $I->seeOptionIsSelected('select[name="Menus[pathInfoVisibleOrder]"]', 'Не учитывать');

        $I->fillField('input[name="Menus[ru__title]"]', 'TestMenu');
        $I->fillField('input[name="Menus[link]"]', 'testlink/aaa');
        $I->click('Сохранить');

        $I->see('Пункт меню создан', '.toast-message');

        self::_selectMenu($I, 'TestMenu');

        $I->see('TestMenu', 'table>tbody>tr>td');
    }

    public function testEditMenu(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);

        self::_selectMenu($I, 'menu3');

        $I->clickPopupMenu('Изменить');

        $I->see('Изменить пункт меню "menu3"', 'h1');

        $I->seeOptionIsSelected('select[name="Menus[parentId]"]', 'БЕЗ РОДИТЕЛЬСКОГО');
        $I->seeOptionIsSelected('select[name="Menus[pathInfoVisibleOrder]"]', 'Не учитывать');
        $I->seeInField('input[name="Menus[ru__title]"]', 'menu3');
        $I->seeInField('input[name="Menus[link]"]', 'l3');

        $I->fillField('input[name="Menus[ru__title]"]', 'menu44');
        $I->fillField('input[name="Menus[link]"]', 'menu44');
        $I->click('Сохранить');

        $I->see('Пункт меню "menu44" отредактирован', '.toast-message');

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu44');
        $I->see('menu44', 'table>tbody>tr>td');
    }

    public function testChangeParentMenu(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);

        self::_selectMenu($I, 'menu3');

        $I->see('menu3', 'div[style*="margin-left: 0px"]');

        $I->clickPopupMenu('Изменить');

        $I->selectOption('select[name="Menus[parentId]"]', 'menu1');
        $I->click('Сохранить');

        $I->see('Пункт меню "menu3" отредактирован', '.toast-message');
        $I->see('menu3', 'div[style*="margin-left: 30px"]');

        $I->clickPopupMenu('Изменить');

        $I->selectOption('select[name="Menus[parentId]"]', 'БЕЗ РОДИТЕЛЬСКОГО');
        $I->click('Сохранить');

        $I->see('Пункт меню "menu3" отредактирован', '.toast-message');
        $I->see('menu3', 'div[style*="margin-left: 0px"]');
    }

    public function testAddChild(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_selectMenu($I, 'menu3');

        $I->clickPopupMenu('Добавить дочерний');

        $I->see('Создать пункт меню', 'h1');

        $I->seeOptionIsSelected('select[name="Menus[parentId]"]', 'menu3');

        $I->fillField('input[name="Menus[ru__title]"]', 'TestMenu');
        $I->fillField('input[name="Menus[link]"]', 'testlink/aaa');
        $I->click('Сохранить');

        $I->see('Пункт меню создан', '.toast-message');

        self::_openMenusPage($I, false);

        self::_selectMenu($I, 'TestMenu');

        $I->see('TestMenu', 'table>tbody>tr>td');
        $I->see('TestMenu', 'div[style*="margin-left: 30px"]');
    }

    public function testDel(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_selectMenu($I, 'menu22');

        $I->see('menu22', 'div[style*="margin-left: 30px"]');
        $I->see('menu221', 'div[style*="margin-left: 60px"]');
        $I->see('menu2231', 'div[style*="margin-left: 90px"]');

        $I->clickPopupMenu('Удалить');

        $I->see('Удалить?');
        $I->clickDlgConfirm();

        $I->see('Пункт меню "menu22" удален', '.toast-message');
        self::_openMenusPage($I, false);

        $I->see('menu221', 'div[style*="margin-left: 30px"]');
        $I->see('menu2231', 'div[style*="margin-left: 60px"]');
    }

    public function testDelRecursive(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_selectMenu($I, 'menu2');

        $I->see('menu2', 'div[style*="margin-left: 0px"]');
        $I->see('menu22', 'div[style*="margin-left: 30px"]');
        $I->see('menu221', 'div[style*="margin-left: 60px"]');
        $I->see('menu2231', 'div[style*="margin-left: 90px"]');

        $I->clickPopupMenu('Удалить с дочерними');

        $I->see('Удалить этот пункт меню и все его дочерние пункты?');
        $I->clickDlgConfirm();

        $I->see('Пункт меню "menu2" удален', '.toast-message');
        self::_openMenusPage($I, false);

        $I->dontSee('menu2', 'table>tbody>tr>td');
        $I->dontSee('menu22', 'table>tbody>tr>td');
        $I->dontSee('menu221', 'table>tbody>tr>td');
        $I->dontSee('menu2231', 'table>tbody>tr>td');
    }

    public function testVisibleForRole(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_selectMenu($I, 'menu23');

        $I->clickPopupMenu('Видимость для ролей');
        $I->see('Роли для которых виден пункт меню "menu23"', 'h1');
        self::_selectRole($I, 'role2');
        $I->see('role2', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu23');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role1');
        $I->see('role1', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu23');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
    }

    public function testAssignVisibleToRole(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_selectMenu($I, 'menu22');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        self::_openMenusPage($I, false);
        self::_assignToRole($I, 'menu22', 'role3');

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu22');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu221');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");
    }

    public function testRevokeVisibleToRole(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_assignToRole($I, 'menu22', 'role3');

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu22');

        $I->clickPopupMenu('Видимость для ролей');

        self::_selectRole($I, 'role3');
        $I->clickPopupMenu('Отменить');
        $I->see('Видимость пункта меню для роли "role3" отменена');

        self::_openMenusPage($I, false);

        self::_selectMenu($I, 'menu22');

        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");
    }

    public function testAssignRecursiveVisibleToRole(\AcceptanceTester $I)
    {
        //контрольная проверка
        self::_openMenusPage($I);
        self::_selectMenu($I, 'menu22');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        self::_openMenusPage($I, false);
        self::_assignToRoleRecursive($I, 'menu22', 'role3');

        //проверка самого меню
        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu22');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");

        //проверка дочернего меню
        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu221');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");

        //проверка внучатого меню
        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu2231');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
    }

    public function testRevokeRecursiveVisibleToRole(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_assignToRoleRecursive($I, 'menu22', 'role3');

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu22');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->clickPopupMenu('Отменить рекурсивно');
        $I->see('Видимость пункта меню для роли "role3" отменена');

        //само меню
        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu22');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        //дочернее меню
        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu221');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        //внучатое меню
        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu2231');
        $I->clickPopupMenu('Видимость для ролей');
        self::_selectRole($I, 'role3');
        $I->see('role3', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");
    }

    public function testVisibleForPathAdd(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);

        self::_addVisibleForPath($I, 'menu3', 'start');

        $I->see('Правило добавлено');

        $I->see('start', 'table>tbody>tr>td');
        $I->see('Содержит', 'table>tbody>tr>td');
    }

    public function testVisibleForPathEdit(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_addVisibleForPath($I, 'menu3', 'start');

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu3');
        $I->clickPopupMenu('Видимость на страницах');
        $I->clickPopupMenu('Изменить');

        $I->see('Изменить правило видимости пункта меню "menu3" на страницах сайта', 'h1');

        $I->seeInField('input[name="MenusVisibleForPathInfo[template]"]', 'start');
        $I->seeOptionIsSelected('select[name="MenusVisibleForPathInfo[rule]"]', 'Содержит');

        $I->fillField('input[name="MenusVisibleForPathInfo[template]"]', 'start2');
        $I->selectOption('select[name="MenusVisibleForPathInfo[rule]"]', 'Равно');

        $I->click('Сохранить');
        $I->see('Правило изменено');

        $I->see('start2', 'table>tbody>tr>td');
        $I->see('Равно', 'table>tbody>tr>td');
    }

    public function testVisibleForPathDel(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_addVisibleForPath($I, 'menu3', 'start');

        self::_openMenusPage($I, false);
        self::_selectMenu($I, 'menu3');
        $I->clickPopupMenu('Видимость на страницах');
        $I->clickPopupMenu('Удалить');
        $I->see('Удалить это правило?');

        $I->clickDlgConfirm();
        $I->see('Правило видимости для пункта меню "menu3" удалено');

        $I->dontSee('start2', 'table>tbody>tr>td');
        $I->dontSee('Равно', 'table>tbody>tr>td');
    }

    public function replaceChildrenVisibleForRole(\AcceptanceTester $I)
    {
        self::_openMenusPage($I);
        self::_selectMenu($I, 'menu2');
        $I->clickPopupMenu('Заменить видимость у дочерних как тут');

        $I->see('Заменить видимость у дочерних пунктов меню?');

        $I->clickDlgConfirm();

        $I->see('Видимость для ролей дочерних пунктов меню установлена', '.toast-message');
    }

    // --------------------------------------------------------

    public static function _addVisibleForPath(\AcceptanceTester $I, $menu, $path)
    {
        self::_selectMenu($I, $menu);

        $I->clickPopupMenu('Видимость на страницах');
        $I->see('Правила видимости пункта меню "' . $menu . '" на страницах сайта', 'h1');

        $I->click('Создать правило');
        $I->see('Добавить правило видимости пункта меню "' . $menu . '" на страницах сайта', 'h1');

        $I->fillField('input[name="MenusVisibleForPathInfo[template]"]', $path);
        $I->click('Сохранить');
    }

    public static function _assignToRoleRecursive(\AcceptanceTester $I, $menu, $role)
    {
        self::_selectMenu($I, $menu);

        $I->clickPopupMenu('Видимость для ролей');

        self::_selectRole($I, $role);
        $I->see($role, ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        $I->clickPopupMenu('Разрешить рекурсивно', 'role-permission-grid-container');
        $I->see('Видимость пункта меню для роли "' . $role . '" предоставлена');
    }

    public static function _assignToRole(\AcceptanceTester $I, $menu, $role)
    {
        self::_selectMenu($I, $menu);

        $I->clickPopupMenu('Видимость для ролей');

        self::_selectRole($I, $role);
        $I->see($role, ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        $I->clickPopupMenu('Разрешить');
        $I->see('Видимость пункта меню для роли "' . $role . '" предоставлена');
    }

    public static function _selectRole(\AcceptanceTester $I, $role)
    {
        $I->fillField('input[name="MenusVisibleForRoleSearch[roleName]"]', $role);
        //$I->wait();
        $I->pressKey('input[name="MenusVisibleForRoleSearch[roleName]"]', WebDriverKeys::ENTER);
    }

    public static function _selectMenu(\AcceptanceTester $I, $menu)
    {
        $I->fillField('input[name="MenuSearch[title]"]', $menu);
        $I->pressKey('input[name="MenuSearch[title]"]', WebDriverKeys::ENTER);
    }

    public static function _openMenusPage(\AcceptanceTester $I, $auth = true)
    {
        if ($auth) {
            self::login($I);
        }
        $I->amOnPage('/admin/menus');

        $I->see('Структура меню сайта', 'h1');
        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
    }
}
