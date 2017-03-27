<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 22.03.2017
 * Time: 9:47
 */

namespace yiicms\tests\unit\tests\components;

use tests\unit\UnitCest;
use yii\rbac\DbManager;
use yiicms\components\core\ArrayHelper;
use yiicms\components\core\RbacHelper;
use yiicms\models\core\Settings;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;

class RbacHelperCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'roles' => RoleFixture::className(),
        ];
    }

    public function testRolesRecursiveForUser(\MyUnitTester $I)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;
        $roles = ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser(-1), 'name');
        $I->assertCount(6, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('role11', $roles);
        $I->assertContains('role12', $roles);
        $I->assertContains('role111', $roles);

        //отнимаем роль
        $auth->revoke($auth->getRole('Super Admin'), -1);

        $roles = ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser(-1), 'name');
        $I->assertCount(5, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('role11', $roles);
        $I->assertContains('role12', $roles);
        $I->assertContains('role111', $roles);

        //отнимаем роль имеющую дочерние роли
        $auth->revoke($auth->getRole('role1'), -1);

        $roles = ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser(-1), 'name');
        $I->assertCount(1, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);

    }
}