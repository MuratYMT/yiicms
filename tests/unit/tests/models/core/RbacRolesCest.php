<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 31.05.2016
 * Time: 9:47
 */

namespace common\unit\test\models\web;

use yiicms\components\core\ArrayHelper;
use yiicms\models\core\MenusForRole;
use yiicms\models\core\RbacRoles;
use yiicms\models\core\Settings;
use yiicms\tests\_data\fixtures\models\core\MenusForRoleFixture;
use yiicms\tests\_data\fixtures\models\core\PermissionFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use tests\unit\UnitCest;

class RbacRolesCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'roles' => RoleFixture::className(),
            'permission' => PermissionFixture::className(),
            'mfr' => MenusForRoleFixture::className(),
        ];
    }

    public function testPermissionForUser(\MyUnitTester $I)
    {
        $permissions = array_keys(RbacRoles::permissionForUser(-1));
        $I->assertCount(6, $permissions);
        $I->assertContains('perm11', $permissions);
        $I->assertContains('perm12', $permissions);
        $I->assertContains('perm111', $permissions);
        $I->assertContains('Admin', $permissions);
        $I->assertContains('AdminPermission', $permissions);
        $I->assertContains('AdminContent', $permissions);

        $permissions = array_keys(RbacRoles::permissionForUser(220));
        $I->assertCount(5, $permissions);
        $I->assertContains('perm21', $permissions);
        $I->assertContains('perm31', $permissions);
        $I->assertContains('Admin', $permissions);
        $I->assertContains('AdminPermission', $permissions);
        $I->assertContains('AdminContent', $permissions);
    }

    public function testAllRoles(\MyUnitTester $I)
    {
        $roles = array_map(
            function ($n) {
                /** @var RbacRoles $n */
                return $n->role->name;
            },
            RbacRoles::allRoles()
        );

        $I->assertCount(10, $roles);

        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains(Settings::get('users.defaultGuestRole'), $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('role11', $roles);
        $I->assertContains('role12', $roles);
        $I->assertContains('role111', $roles);
        $I->assertContains('role2', $roles);
        $I->assertContains('role3', $roles);
    }

    public function testGetChildrenRoles(\MyUnitTester $I)
    {
        $role = RbacRoles::findOne('role1');
        $children = ArrayHelper::getColumn($role->childrenRoles(), 'name');
        $I->assertCount(2, $children);
        $I->assertContains('role11', $children);
        $I->assertContains('role12', $children);

        $children = $role->childsRolesNames;
        $I->assertCount(2, $children);
        $I->assertContains('role11', $children);
        $I->assertContains('role12', $children);
    }

    public function testGetPermissions(\MyUnitTester $I)
    {
        $role = RbacRoles::findOne('role1');
        $perms = ArrayHelper::getColumn($role->permissions, 'name');
        $I->assertCount(2, $perms);
        $I->assertContains('perm11', $perms);
        $I->assertContains('perm12', $perms);

        $perms = $role->permissionsNames;
        $I->assertCount(2, $perms);
        $I->assertContains('perm11', $perms);
        $I->assertContains('perm12', $perms);
    }

    public function testGetPermissionsRecursive(\MyUnitTester $I)
    {
        $role = RbacRoles::findOne('role1');
        $perms = ArrayHelper::getColumn($role->permissionsRecursive, 'name');

        $I->assertCount(3, $perms);
        $I->assertContains('perm11', $perms);
        $I->assertContains('perm12', $perms);
        $I->assertContains('perm111', $perms);
    }

    public function testNotAssgnmentPermissions(\MyUnitTester $I)
    {
        $role = RbacRoles::findOne('role1');
        $perms = ArrayHelper::getColumn($role->notAssigmentPermissions, 'name');
        $I->assertCount(8, $perms);
        $I->assertContains('perm111', $perms);
        $I->assertContains('perm21', $perms);
        $I->assertContains('perm31', $perms);
        $I->assertContains('perm101', $perms);
        $I->assertContains('perm102', $perms);
        $I->assertContains('Admin', $perms);
        $I->assertContains('AdminPermission', $perms);
        $I->assertContains('AdminContent', $perms);

        $I->assertNotContains('perm11', $perms);
        $I->assertNotContains('perm12', $perms);
    }

    public function testScenariosChilds(\MyUnitTester $I)
    {
        $role = RbacRoles::findOne('role1');
        $role->scenario = RbacRoles::SC_ADD_CHILDS;
        $role->childsRolesNames = 'role4';
        $I->assertTrue($role->save());

        $role = RbacRoles::findOne('role1');
        $children = ArrayHelper::getColumn($role->childrenRoles(), 'name');
        $I->assertCount(3, $children);
        $I->assertContains('role11', $children);
        $I->assertContains('role12', $children);
        $I->assertContains('role4', $children);

        $role->scenario = RbacRoles::SC_REMOVE_CHILDS;
        $role->childsRolesNames = 'role12';
        $I->assertTrue($role->save());

        $role = RbacRoles::findOne('role1');
        $children = ArrayHelper::getColumn($role->childrenRoles(), 'name');
        $I->assertCount(2, $children);
        $I->assertContains('role11', $children);
        $I->assertContains('role4', $children);

        $role = RbacRoles::findOne('role11');
        $role->scenario = RbacRoles::SC_ADD_CHILDS;
        $role->childsRolesNames = 'role1';
        $I->assertFalse($role->save());
        $I->assertTrue($role->hasErrors('childsRolesNames'));
    }

    public function testScenariosPermissions(\MyUnitTester $I)
    {
        $role = RbacRoles::findOne('role1');
        $role->scenario = RbacRoles::SC_ADD_PERMISSION;
        $role->permissionsNames = 'perm101';
        $I->assertTrue($role->save());

        $role = RbacRoles::findOne('role1');
        $perms = ArrayHelper::getColumn($role->permissions, 'name');
        $I->assertCount(3, $perms);
        $I->assertContains('perm11', $perms);
        $I->assertContains('perm12', $perms);
        $I->assertContains('perm101', $perms);

        $role->scenario = RbacRoles::SC_REMOVE_PERMISSION;
        $role->permissionsNames = 'perm12';
        $I->assertTrue($role->save());

        $role = RbacRoles::findOne('role1');
        $perms = ArrayHelper::getColumn($role->permissions, 'name');
        $I->assertCount(2, $perms);
        $I->assertContains('perm11', $perms);
        $I->assertContains('perm101', $perms);
    }

    public function testCreate(\MyUnitTester $I)
    {
        $role = new RbacRoles();
        $role->scenario = RbacRoles::SC_EDIT;
        $role->load(['zz' => ['name' => 'roleTest', 'description' => 'test']], 'zz');

        $I->assertTrue($role->save());

        $roles = array_map(
            function ($n) {
                /** @var RbacRoles $n */
                return $n->role->name;
            },
            RbacRoles::allRoles()
        );

        $I->assertCount(11, $roles);

        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains(Settings::get('users.defaultGuestRole'), $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains('role1', $roles);
        $I->assertContains('role11', $roles);
        $I->assertContains('role12', $roles);
        $I->assertContains('role111', $roles);
        $I->assertContains('role2', $roles);
        $I->assertContains('role3', $roles);
        $I->assertContains('role4', $roles);
        $I->assertContains('roleTest', $roles);
    }

    public function testDelete(\MyUnitTester $I)
    {
        $role = RbacRoles::findOne('role1');

        $menus = MenusForRole::findAll(['roleName' => $role->name]);

        $I->assertCount(2, $menus);
        $role->delete();
        $auth = \Yii::$app->authManager;
        $I->assertNull($auth->getRole('role1'));

        $roles = array_map(
            function ($n) {
                /** @var RbacRoles $n */
                return $n->role->name;
            },
            RbacRoles::allRoles()
        );

        $I->assertCount(9, $roles);

        $I->assertContains(Settings::get('users.defaultRegisteredRole'), $roles);
        $I->assertContains(Settings::get('users.defaultGuestRole'), $roles);
        $I->assertContains('Super Admin', $roles);
        $I->assertContains('role11', $roles);
        $I->assertContains('role12', $roles);
        $I->assertContains('role111', $roles);
        $I->assertContains('role2', $roles);
        $I->assertContains('role3', $roles);
        $I->assertContains('role4', $roles);


        $menus = MenusForRole::findAll(['roleName' => $role->name]);
        $I->assertCount(0, $menus);
    }
}
