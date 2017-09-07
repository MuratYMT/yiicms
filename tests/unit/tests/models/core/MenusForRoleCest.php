<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.01.2017
 * Time: 9:37
 */

namespace common\unit\test\models\web;

use yiicms\components\YiiCms;
use yiicms\models\core\Menus;
use yiicms\models\core\MenusForRole;
use yiicms\tests\_data\fixtures\models\core\MenusFixture;
use yiicms\tests\_data\fixtures\models\core\MenusForRoleFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use tests\unit\UnitCest;

class MenusForRoleCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'menus' => MenusFixture::className(),
            'roles' => RoleFixture::className(),
            'mfr' => MenusForRoleFixture::className(),
        ];
    }

    public function testAddAsParent(\MyUnitTester $I)
    {
        $menuParent = $this->_menus('m23');

        $menu = new Menus();
        $menu->parentId = $menuParent->menuId;
        $menu->title = 'menuChild';

        $I->assertTrue(YiiCms::$app->menuService->save($menu));

        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role3', 'menuId' => $menu->menuId]);
        $I->seeRecord(MenusForRole::className(), ['roleName' => 'role1', 'menuId' => $menu->menuId]);
        $I->dontSeeRecord(MenusForRole::className(), ['roleName' => 'role2', 'menuId' => $menu->menuId]);
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