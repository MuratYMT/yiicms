<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 14.06.2016
 * Time: 16:03
 */

namespace tests\acceptance;

use Helper\Fixture;
use yiicms\tests\_data\fixtures\models\core\PermissionFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use yiicms\tests\_data\fixtures\models\core\SettingsFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class AcceptanceCest
{
    /** @var  \AcceptanceTester */
    public $tester;

    /** @var  Fixture */
    protected $fixtures;

    public static function _fixtures()
    {
        return array_merge(static::_globalFixture(), static::_cestFixtures());
    }

    public static function _cestFixtures()
    {
        return [];
    }

    public function _before(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public static function _globalFixture()
    {
        return [
            PermissionFixture::className(),
            UsersFixture::className(),
            RoleFixture::className(),
            SettingsFixture::className(),
        ];
    }

    public function _after(\AcceptanceTester $I)
    {
    }

    protected static function login(\AcceptanceTester $I, $user = 'SXSXS')
    {
        $I->amOnPage('login');

        $I->fillField('#loginform-email', $user);
        $I->fillField('#loginform-password', $user);
        $I->click('form button[type=submit]');
        $I->see('Вы успешно вошли');
    }

    protected static function logout(\AcceptanceTester $I)
    {
        $I->amOnPage('logout');
        $I->click('form button[type=submit]');
        $I->see('Вы успешно вышли');
    }
}
