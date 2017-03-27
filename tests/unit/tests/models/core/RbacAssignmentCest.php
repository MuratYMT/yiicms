<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 30.05.2016
 * Time: 16:12
 */

namespace common\unit\test\models\web;

use yiicms\components\core\ArrayHelper;
use yiicms\models\core\RbacAssignment;
use yiicms\models\core\RbacRoles;
use yiicms\models\core\Settings;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use tests\unit\UnitCest;
use yii\rbac\DbManager;

class RbacAssignmentCest extends UnitCest
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
        $roles = RbacAssignment::rolesNamesRecursiveForUser(-1);
        $I->assertCount(6, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('role11', $roles);
        $I->assertContains('role12', $roles);
        $I->assertContains('role111', $roles);

        //отнимаем роль
        $auth->revoke($auth->getRole('Super Admin'), -1);

        $roles = RbacAssignment::rolesNamesRecursiveForUser(-1);
        $I->assertCount(5, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('role11', $roles);
        $I->assertContains('role12', $roles);
        $I->assertContains('role111', $roles);

        //отнимаем роль имеющую дочерние роли
        $auth->revoke($auth->getRole('role1'), -1);

        $roles = RbacAssignment::rolesNamesRecursiveForUser(-1);
        $I->assertCount(1, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);

    }

    public function testRolesRecursiveForUser2(\MyUnitTester $I)
    {
        $roles = RbacAssignment::rolesNamesRecursiveForUser(-1);
        $I->assertCount(6, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('role11', $roles);
        $I->assertContains('role12', $roles);
        $I->assertContains('role111', $roles);


        $role = RbacRoles::findOne('role1');
        $role->scenario = RbacRoles::SC_REMOVE_CHILDS;
        $role->childsRolesNames = 'role11';
        $I->assertTrue($role->save());

        $roles = RbacAssignment::rolesNamesRecursiveForUser(-1);
        $I->assertCount(4, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('role12', $roles);
    }

    public function testRolesForUser(\MyUnitTester $I)
    {
        $roles = RbacAssignment::rolesNamesForUser(-1);

        $I->assertCount(3, $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains('role1', $roles);

        $roles = RbacAssignment::rolesNamesForUser(220);

        $I->assertCount(4, $roles);
        $I->assertContains('role3', $roles);
        $I->assertContains('role2', $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
    }

    public function testFindOne(\MyUnitTester $I)
    {
        $assign = RbacAssignment::findOne(220);

        $roles = $assign->rolesNames;

        $I->assertCount(4, $roles);
        $I->assertContains('role3', $roles);
        $I->assertContains('role2', $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
    }

    public function testScenarios(\MyUnitTester $I)
    {
        $assign = RbacAssignment::findOne(220);

        $assign->scenario = RbacAssignment::SC_ROLES_ADD;
        $assign->rolesNames = 'role1';

        $I->assertTrue($assign->save());

        $rolesObjs = \Yii::$app->authManager->getRolesByUser(220);
        $I->assertCount(5, $rolesObjs);
        $roles = ArrayHelper::getColumn($rolesObjs, 'name');
        $I->assertContains('role3', $roles);
        $I->assertContains('role2', $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);

        $assign->scenario = RbacAssignment::SC_ROLES_REMOVE;
        $assign->rolesNames = 'role3';

        $I->assertTrue($assign->save());

        $rolesObjs = \Yii::$app->authManager->getRolesByUser(220);
        $I->assertCount(4, $rolesObjs);
        $roles = ArrayHelper::getColumn($rolesObjs, 'name');
        $I->assertContains('role2', $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
    }
}
