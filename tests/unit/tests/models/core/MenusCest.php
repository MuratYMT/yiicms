<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.06.2016
 * Time: 12:36
 */

namespace common\unit\test\models\web;

use yiicms\models\core\Menus;
use yiicms\models\core\MenusForRole;
use yiicms\models\core\MenusVisibleForPathInfo;
use yiicms\tests\_data\fixtures\models\core\MenusFixture;
use yiicms\tests\_data\fixtures\models\core\MenusForRoleFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use tests\unit\UnitCest;

/**
 * Class MenusCest
 * @package tests\unit\test\models\web
 * @method Menus menus($id)
 */
class MenusCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'menus' => MenusFixture::className(),
            'roles' => RoleFixture::className(),
            'mfr' => MenusForRoleFixture::className(),
        ];
    }

    public function testGrantRevoke(\MyUnitTester $I)
    {
        $menu = $this->_menus('m2');

        $I->assertNull(MenusForRole::findOne(['menuId' => $menu->menuId, 'roleName' => 'role3']));
        $I->assertTrue($menu->grant('role3'));
        $I->assertNotNull(MenusForRole::findOne(['menuId' => $menu->menuId, 'roleName' => 'role3']));
        $I->assertTrue($menu->revoke('role3'));
        $I->assertNull(MenusForRole::findOne(['menuId' => $menu->menuId, 'roleName' => 'role3']));

        $menu22 = $this->_menus('m22');
        $menu222 = $this->_menus('m222');

        $I->assertNull(MenusForRole::findOne(['menuId' => $menu22->menuId, 'roleName' => 'role3']));
        $I->assertNull(MenusForRole::findOne(['menuId' => $menu222->menuId, 'roleName' => 'role3']));

        $I->assertTrue($menu->grant('role3', true));
        $I->assertNotNull(MenusForRole::findOne(['menuId' => $menu->menuId, 'roleName' => 'role3']));
        $I->assertNotNull(MenusForRole::findOne(['menuId' => $menu22->menuId, 'roleName' => 'role3']));
        $I->assertNotNull(MenusForRole::findOne(['menuId' => $menu222->menuId, 'roleName' => 'role3']));

        $I->assertTrue($menu->revoke('role3', true));
        $I->assertNull(MenusForRole::findOne(['menuId' => $menu->menuId, 'roleName' => 'role3']));
        $I->assertNull(MenusForRole::findOne(['menuId' => $menu22->menuId, 'roleName' => 'role3']));
        $I->assertNull(MenusForRole::findOne(['menuId' => $menu222->menuId, 'roleName' => 'role3']));
    }

    public function testDelete(\MyUnitTester $I)
    {
        $menu = $this->_menus('m2');
        $I->assertNotFalse($menu->delete());

        $I->assertNull($menu21 = Menus::findOne(200));
        $I->assertNotNull($menu21 = Menus::findOne(220));
        $I->assertEquals(0, $menu21->parentId);
        $I->assertNull($menu21->parent);

        $I->assertNotNull($menu22 = Menus::findOne(220));
        $I->assertEquals(0, $menu22->parentId);
        $I->assertNull($menu22->parent);

        $I->assertNotNull($menu23 = Menus::findOne(220));
        $I->assertEquals(0, $menu23->parentId);
        $I->assertNull($menu23->parent);
    }

    public function testDeleteWithChildren(\MyUnitTester $I)
    {
        $menu = $this->_menus('m2');
        $I->assertEquals(8, $menu->deleteRecursive());
        $I->assertNull(Menus::findOne(200));
        $I->assertNull(Menus::findOne(210));
        $I->assertNull(Menus::findOne(220));
        $I->assertNull(Menus::findOne(221));
        $I->assertNull(Menus::findOne(222));
        $I->assertNull(Menus::findOne(223));
        $I->assertNull(Menus::findOne(2231));
        $I->assertNull(Menus::findOne(230));
    }

    public function testBranchForUser(\MyUnitTester $I)
    {
        \Yii::$app->request->pathInfo = 'index';

        $menus = Menus::branchForUser(220, 200);
        $I->assertCount(5, $menus);

        foreach ($menus as $menu) {
            $I->assertTrue(in_array($menu->menuId, [210, 230, 220, 222, 221], true));
        }

        //210 запрещаем показ по пути содеражащему nde
        $mfp = new MenusVisibleForPathInfo([
            'menuId' => 210,
            'rule' => MenusVisibleForPathInfo::RULE_CONTAIN,
            'template' => 'nde',
        ]);
        $I->assertTrue($mfp->save());
        $menu = Menus::findOne(210);
        $menu->pathInfoVisibleOrder = MenusVisibleForPathInfo::VISIBLE_ALLOW_DENY;
        $I->assertTrue($menu->save());

        $menus = Menus::branchForUser(220, 200);
        $I->assertCount(4, $menus);
        $I->assertNotContains(210, array_keys($menus));
        foreach ($menus as $menu) {
            $I->assertTrue(in_array($menu->menuId, [230, 220, 222, 221], true));
        }

        //210 разрешаем показ только по пути содеражащему nde
        $menu = Menus::findOne(210);
        $menu->pathInfoVisibleOrder = MenusVisibleForPathInfo::VISIBLE_DENY_ALLOW;
        $I->assertTrue($menu->save());

        $menus = Menus::branchForUser(220, 200);
        $I->assertCount(5, $menus);
        $I->assertContains(210, array_keys($menus));
        foreach ($menus as $menu) {
            $I->assertTrue(in_array($menu->menuId, [210, 230, 220, 222, 221], true));
        }

        \Yii::$app->request->pathInfo = 'test';

        $menus = Menus::branchForUser(220, 200);
        $I->assertCount(4, $menus);
        $I->assertNotContains(210, array_keys($menus));
        foreach ($menus as $menu) {
            $I->assertTrue(in_array($menu->menuId, [230, 220, 222, 221], true));
        }
    }

    public function testBranchForUserWithRecursiveRole(\MyUnitTester $I)
    {
        \Yii::$app->request->pathInfo = 'index';

        $auth = \Yii::$app->authManager;
        $auth->revoke($auth->getRole('role3'), 220);
        $auth->assign($auth->getRole('role1'), 220);

        $menus = Menus::branchForUser(220, 200);
        $I->assertCount(5, $menus);

        foreach ($menus as $menu) {
            $I->assertTrue(in_array($menu->menuId, [230, 210, 220, 222, 221], true));
        }

        $mfr = MenusForRole::findOne(['menuId' => 230, 'roleName' => 'role1']);
        $I->assertNotFalse($mfr->delete());

        $menus = Menus::branchForUser(220, 200);
        $I->assertCount(4, $menus);

        foreach ($menus as $menu) {
            $I->assertTrue(in_array($menu->menuId, [210, 220, 222, 221], true));
        }

        $mfr = new MenusForRole(['menuId' => 230, 'roleName' => 'role111']);
        $I->assertTrue($mfr->save());

        $menus = Menus::branchForUser(220, 200);
        $I->assertCount(5, $menus);

        foreach ($menus as $menu) {
            $I->assertTrue(in_array($menu->menuId, [230, 210, 220, 222, 221], true));
        }
    }

    public function replaceChildrenVisibleForRole(\MyUnitTester $I)
    {
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => 200]);
        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => 200]);

        $mfr = new MenusForRole(['roleName' => 'role1', 'menuId' => 200]);
        $I->assertTrue($mfr->save());
        $mfr2 = MenusForRole::findOne(['roleName' => 'role2', 'menuId' => 200]);
        $I->assertNotFalse($mfr2->delete());

        //сам пункт меню
        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => 200]);
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => 200]);

        //ветка
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => 220]);
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => 223]);
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => 2231]);

        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => 220]);
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => 223]);
        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => 2231]);

        //
        $I->assertTrue($this->_menus('m2')->replaceChildrenVisibleForRole());

        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => 220]);
        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => 223]);
        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => 2231]);

        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => 220]);
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => 223]);
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => 2231]);
    }

    /**
     * @param string $id
     * @return Menus
     */
    public function _menus($id)
    {
        return $this->tester->grabFixture('menus', $id);
    }
}
