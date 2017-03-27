<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.06.2016
 * Time: 12:05
 */

namespace tests\acceptance\modules\admin;

use Facebook\WebDriver\WebDriverKeys;
use tests\acceptance\AcceptanceCest;
use yiicms\models\core\Settings;
use yiicms\tests\_data\fixtures\models\core\PermissionFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;

class RolesCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            PermissionFixture::className(),
            RoleFixture::className(),
        ];
    }

    public function testIndex(\AcceptanceTester $I)
    {
        self::_openRolePage($I);

        $I->see('Super Admin');
        $I->see(Settings::get('users.defaultRegisteredRole'));
        $I->see(Settings::get('users.defaultGuestRole'));

        $I->see('role1');
        $I->see('role11');
        $I->see('role12');
        $I->see('role111');
        $I->see('role2');
        $I->see('role3');
        $I->see('role4');

        self::_selectRole($I, 'role2');
        $I->dontSee('Super Admin');
        $I->dontSee(Settings::get('users.defaultRegisteredRole'));
        $I->dontSee(Settings::get('users.defaultGuestRole'));

        $I->dontSee('role1');
        $I->dontSee('role11');
        $I->dontSee('role12');
        $I->dontSee('role111');
        $I->see('role2');
        $I->dontSee('role3');
        $I->dontSee('role4');
    }

    public function testEditRole(\AcceptanceTester $I)
    {
        self::_openRolePage($I);
        self::_selectRole($I, 'role2');

        $I->clickPopupMenu('Изменить');
        $I->see('Редактирование роли "role2"', 'h1');
        $I->seeInField('input[name="RoleEdit[name]"]', 'role2');
        $I->fillField('input[name="RoleEdit[name]"]', 'role2555');
        $I->fillField('input[name="RoleEdit[description]"]', 'role 2555 description');
        $I->click('Сохранить');

        $I->see('Роль "role2555" изменена');
        $I->see('role2555', 'table>tbody>tr>td');
        $I->see('role 2555 description', 'table>tbody>tr>td');
    }

    public function testCreateRole(\AcceptanceTester $I)
    {
        self::_openRolePage($I);

        $I->click('Создать роль');

        $I->see('Создание новой роли', 'h1');
        $I->fillField('input[name="RoleEdit[name]"]', 'role4555');
        $I->fillField('input[name="RoleEdit[description]"]', 'role 4555 description');
        $I->click('Сохранить');

        $I->see('Роль "role4555" создана');
        $I->see('role4555', 'table>tbody>tr>td');
        $I->see('role 4555 description', 'table>tbody>tr>td');
    }

    public function testDeleteRole(\AcceptanceTester $I)
    {
        self::_openRolePage($I);
        self::_selectRole($I, 'role2');
        $I->see('role2', 'table>tbody>tr>td');
        $I->clickPopupMenu('Удалить', null, null, 1);

        $I->see('Удалить?');
        $I->clickDlgConfirm();

        $I->see('Роль "role2" удалена');
        $I->dontSee('role2', 'table>tbody>tr>td');
    }

    public function testAddChildRole(\AcceptanceTester $I)
    {
        self::_openRolePage($I);

        self::_addChildRole($I, 'role2', 'role3');

        self::_openRolePage($I, false);

        $I->see('role3', 'div[style*="margin-left: 30px"]');
    }

    public function testDelChildRole(\AcceptanceTester $I)
    {
        self::_openRolePage($I);
        $I->see('role11', 'div[style*="margin-left: 30px"]');
        $I->see('role111', 'div[style*="margin-left: 60px"]');

        self::_delChildRole($I, 'role1', 'role11');

        self::_openRolePage($I, false);

        $I->see('role11', 'div[style*="margin-left: 0px"]');
        $I->see('role111', 'div[style*="margin-left: 30px"]');
    }

    public function testAllPermissionForRole(\AcceptanceTester $I)
    {
        self::_openRolePage($I);
        self::_selectRole($I, 'role1');
        $I->clickPopupMenu(' Результирующие разрешения');

        $I->see('Разрешения назначенные роли "role1"', 'h1');

        $I->see('perm11', 'table>tbody>tr>td');
        $I->see('perm12', 'table>tbody>tr>td');
        $I->see('perm111', 'table>tbody>tr>td');
        $I->dontSee('perm31', 'table>tbody>tr>td');

        self::_openRolePage($I, false);
        self::_addChildRole($I, 'role1', 'role3');

        self::_openRolePage($I, false);
        self::_selectRole($I, 'role1');
        $I->clickPopupMenu('Результирующие разрешения');

        $I->see('Разрешения назначенные роли "role1"', 'h1');

        $I->see('perm11', 'table>tbody>tr>td');
        $I->see('perm12', 'table>tbody>tr>td');
        $I->see('perm111', 'table>tbody>tr>td');
        $I->see('perm31', 'table>tbody>tr>td');
    }

    public function testPermissionForRole(\AcceptanceTester $I)
    {
        self::_openPermissionPage($I, 'role2');

        $I->see('perm21', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('perm11', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");
    }

    public function testAddPermissionToRole(\AcceptanceTester $I)
    {
        self::_openPermissionPage($I, 'role2');

        $I->see('perm101', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");

        $I->fillField('input[name="PermissionSearch[name]"]', 'perm101');
        $I->pressKey('input[name="PermissionSearch[name]"]', WebDriverKeys::ENTER);

        $I->clickPopupMenu('Назначить');

        $I->see('Разрешение "perm101" назначено', '.toast-message');
        $I->see('perm101', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
    }

    public function testRevokePermissionFromRole(\AcceptanceTester $I)
    {
        self::_openPermissionPage($I, 'role2');

        $I->see('perm21', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");

        $I->fillField('input[name="PermissionSearch[name]"]', 'perm21');
        $I->pressKey('input[name="PermissionSearch[name]"]', WebDriverKeys::ENTER);

        $I->clickPopupMenu('Отозвать');

        $I->see('Разрешение "perm21" отозвано', '.toast-message');
        $I->see('perm21', ".//*[@id='role-permission-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-remove')]]");
    }

    // --------------------------------------------------

    public static function _openPermissionPage(\AcceptanceTester $I, $role, $auth = true)
    {
        self::_openRolePage($I, $auth);

        self::_selectRole($I, $role);
        $I->clickPopupMenu('Назначенные разрешения');
        $I->see('Разрешения назначенные роли "' . $role . '"', 'h1');
    }

    public static function _addChildRole(\AcceptanceTester $I, $parentRole, $childRole)
    {
        self::_selectRole($I, $parentRole);

        $I->clickPopupMenu('Добавить дочернюю');

        $I->see('Добавление дочерних ролей для "' . $parentRole . '"', 'h1');
        $I->click('a[href*="childRole=' . $childRole . '"]');

        $I->see('Роль "' . $childRole . '" добавлена как дочерняя', '.toast-message');
    }

    public static function _delChildRole(\AcceptanceTester $I, $parentRole, $childRole)
    {
        self::_selectRole($I, $parentRole);

        $I->clickPopupMenu('Удалить дочерние');

        $I->see('Удаление дочерних ролей из "' . $parentRole . '"', 'h1');

        $I->click('a[href*="childRole=' . $childRole . '"]', null, 1);

        $I->see('Удалить из списка дочерних?');
        $I->clickDlgConfirm();

        $I->see('Роль "' . $childRole . '" удалена из дочерних', '.toast-message');
    }

    public static function _selectRole(\AcceptanceTester $I, $role)
    {
        $I->fillField('input[name="RolesSearch[name]"]', $role);
        $I->pressKey('input[name="RolesSearch[name]"]', WebDriverKeys::ENTER);
        $I->dontSee('404', 'header');
        $I->see($role);
    }

    public static function _openRolePage(\AcceptanceTester $I, $auth = true)
    {
        if ($auth) {
            self::login($I);
        }

        $I->amOnPage('/admin/roles');

        $I->see('Существующие роли', 'h1');
        $I->dontSee(404, 'header');
    }
}
