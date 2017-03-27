<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 16.06.2016
 * Time: 14:56
 */

namespace tests\acceptance\modules\admin;

use Facebook\WebDriver\WebDriverKeys;
use tests\acceptance\AcceptanceCest;
use yiicms\models\core\Settings;
use yiicms\tests\_data\fixtures\models\core\PermissionFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class UsersCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            PermissionFixture::className(),
            UsersFixture::className(),
            RoleFixture::className(),
        ];
    }

    public function testIndex(\AcceptanceTester $I)
    {
        self::login($I);

        $I->amOnPage('/admin/users');

        $I->see('Пользователи', 'h1');
        $I->dontSee('404');

        $I->see('SXSXS');
        $I->see('SuperUser');
        $I->see('SimpleUser');
        $I->see('NOT_ACTIVE');
        $I->fillField('input[name="UsersSearch[login]"]', 'SXSXS');
        $I->pressKey('input[name="UsersSearch[login]"]', WebDriverKeys::ENTER);

        $I->see('SXSXS');
        $I->dontSee('NOT_ACTIVE');
    }

    public function testChangePassword(\AcceptanceTester $I)
    {
        self::login($I);

        $I->amOnPage('/admin/users');

        $I->fillField('input[name="UsersSearch[login]"]', 'SXSXS');
        $I->pressKey('input[name="UsersSearch[login]"]', WebDriverKeys::ENTER);

        $I->clickPopupMenu('Сменить пароль');

        $I->fillField('#changepasswordform-password', 'ZZZZZZ');
        $I->fillField('#changepasswordform-password2', 'ZZZZZZ2');
        $I->click('Сменить пароль');

        $I->see('Значение «Подтверждение» должно быть равно «Пароль»');

        $I->fillField('#changepasswordform-password', 'ZZZZZZ');
        $I->fillField('#changepasswordform-password2', 'ZZZZZZ');
        $I->click('Сменить пароль');

        $I->see('Пароль изменен', '.toast-message');

        self::logout($I);

        $I->amOnPage('login');

        $I->fillField('#loginform-email', 'SXSXS');
        $I->fillField('#loginform-password', 'ZZZZZZ');
        $I->click('form button[type=submit]');

        $I->see('Вы успешно вошли');
    }

    public function testRoles(\AcceptanceTester $I)
    {
        self::_openRolePage($I);

        $I->dontSee('role1', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('role2', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('role3', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see(Settings::get('users.defaultRegisteredRole'),
            ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('Super Admin', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
    }

    public function testRoleRevoke(\AcceptanceTester $I)
    {
        self::_openRolePage($I);

        self::_selectRole($I, 'role2');

        $I->clickPopupMenu('Отозвать');

        $I->see('Отозвать у пользователя выбранные роли?');

        $I->clickDlgConfirm();

        $I->dontSee('role2', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('role3', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see(Settings::get('users.defaultRegisteredRole'),
            ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('Super Admin', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
    }

    public function testRoleAssign(\AcceptanceTester $I)
    {
        self::_openRolePage($I);

        self::_selectRole($I, 'role1');
        $I->clickPopupMenu('Назначить');

        $I->see('role2', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('role3', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('role1', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see(Settings::get('users.defaultRegisteredRole'),
            ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
        $I->see('Super Admin', ".//*[@id='user-roles-grid-container']/table/tbody/tr[.//span[contains(@class, 'glyphicon-ok')]]");
    }

    public function testUserPermission(\AcceptanceTester $I)
    {
        self::_openPermissionPage($I, true, 'SuperUser');

        self::_selectPermisiion($I, 'perm11');
        $I->see('perm11', 'table>tbody>tr>td');
        $I->see('role1', './/*[@id=\'user-permission-grid-container\']/table/tbody/tr[.//td[contains(., "perm11")]]');

        self::_openPermissionPage($I, false, 'SuperUser');
        self::_selectPermisiion($I, 'perm12');
        $I->see('perm12', 'table>tbody>tr>td');
        $I->see('role1', './/*[@id=\'user-permission-grid-container\']/table/tbody/tr[.//td[contains(., "perm12")]]');

        self::_openPermissionPage($I, false, 'SuperUser');
        self::_selectPermisiion($I, 'perm111');
        $I->see('perm111', 'table>tbody>tr>td');
        $I->see('role11', './/*[@id=\'user-permission-grid-container\']/table/tbody/tr[.//td[contains(., "perm111")]]');

        self::_openPermissionPage($I, false, 'SuperUser');
        self::_selectPermisiion($I, 'perm21');
        $I->dontSee('perm21', 'table>tbody>tr>td');
    }

    public function testPermissionAssign(\AcceptanceTester $I)
    {
        self::_openPermissionPage($I, true);

        self::_selectPermisiion($I, 'perm11');
        $I->dontSee('perm11', 'table>tbody>tr>td');

        self::_openRolePage($I, false);
        self::_selectRole($I, 'role1');
        $I->clickPopupMenu('Назначить');

        self::_openPermissionPage($I, false);
        self::_selectPermisiion($I, 'perm11');

        $I->see('perm11', 'table>tbody>tr>td');
        $I->see('role1', './/*[@id=\'user-permission-grid-container\']/table/tbody/tr[.//td[contains(., "perm11")]]');
    }

    public function testPermissionRevoke(\AcceptanceTester $I)
    {
        self::_openPermissionPage($I, true);

        self::_selectPermisiion($I, 'perm21');
        $I->see('perm21', 'table>tbody>tr>td');
        $I->see('role2', './/*[@id=\'user-permission-grid-container\']/table/tbody/tr[.//td[contains(., "perm21")]]');

        self::_openRolePage($I, false);
        self::_selectRole($I, 'role2');

        $I->clickPopupMenu('Отозвать');

        $I->clickDlgConfirm();

        self::_openPermissionPage($I, false);
        self::_selectPermisiion($I, 'perm21');

        $I->dontSee('perm21', 'table>tbody>tr>td');
    }

    // ------------------------------------------------

    public static function _selectRole(\AcceptanceTester $I, $role)
    {
        $I->fillField('input[name="RolesSearch[name]"]', $role);
        $I->pressKey('input[name="RolesSearch[name]"]', WebDriverKeys::ENTER);

        $I->dontSee('404', 'header');
    }

    public static function _selectPermisiion(\AcceptanceTester $I, $permission)
    {
        $I->fillField('input[name="PermissionSearch[name]"]', $permission);
        $I->pressKey('input[name="PermissionSearch[name]"]', WebDriverKeys::ENTER);

        $I->dontSee('404', 'header');
    }

    public static function _openPermissionPage(\AcceptanceTester $I, $auth = true, $login = 'SXSXS')
    {
        if ($auth) {
            self::login($I, $login);
        }

        $I->amOnPage('/admin/users');

        $I->fillField('input[name="UsersSearch[login]"]', $login);
        $I->pressKey('input[name="UsersSearch[login]"]', WebDriverKeys::ENTER);

        $I->clickPopupMenu('Разрешения');

        $I->see('Разрешения назначенные пользователю "' . $login . '"', 'h1');
    }

    public static function _openRolePage(\AcceptanceTester $I, $auth = true, $login = 'SXSXS')
    {
        if ($auth) {
            self::login($I, $login);
        }

        $I->amOnPage('/admin/users');

        $I->fillField('input[name="UsersSearch[login]"]', $login);
        $I->pressKey('input[name="UsersSearch[login]"]', WebDriverKeys::ENTER);

        $I->clickPopupMenu('a[title="Управление ролями пользователя"]');

        $I->see('Роли назначенные пользователю "' . $login . '"', 'h1');
    }
}
