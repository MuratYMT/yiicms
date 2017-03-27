<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.05.2016
 * Time: 11:01
 */

namespace tests\unit;

use yiicms\models\core\Users;
use yiicms\tests\_data\fixtures\models\core\PermissionFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use yiicms\tests\_data\fixtures\models\core\SettingsFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class UnitCest
{
    /** @var  \MyUnitTester */
    public $tester;

    public static function _fixtures()
    {
        return array_merge(static::_globalFixture(), static::_cestFixtures());
    }

    public static function _cestFixtures()
    {
        return [];
    }

    public function _before(\MyUnitTester $I)
    {
        $this->tester = $I;
        \Yii::$app->user->login(Users::findIdentity(220));
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

    public function _after()
    {
        \Yii::$app->user->logout();
    }
}
