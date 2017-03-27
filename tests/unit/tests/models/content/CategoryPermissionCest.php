<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 06.02.2017
 * Time: 23:13
 */

namespace common\unit\test\models\content;

use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yiicms\tests\_data\fixtures\models\content\CategoryFixture;
use yiicms\tests\_data\fixtures\models\content\PageFixture;
use yiicms\tests\_data\fixtures\models\content\PageInCategoryFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use tests\unit\UnitCest;

class CategoryPermissionCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'category' => CategoryFixture::className(),
            'roles' => RoleFixture::className(),
            'pages' => PageFixture::className(),
            'pin' => PageInCategoryFixture::className(),
        ];
    }

    public function testCompileParentTree(\MyUnitTester $I)
    {
        CategoryPermission::clearCompiledTree();
        $tree = [
            1 => [11 => [111, 112]],
            2,
            3 => [31, 32 => [321, 322 => [3221, 3222], 323], 33],
        ];
        $compiledTree = [
            1 => [],
            11 => [1],
            111 => [11, 1],
            112 => [11, 1],
            2 => [],
            3 => [],
            31 => [3],
            32 => [3],
            321 => [32, 3],
            322 => [32, 3],
            3221 => [322, 32, 3],
            3222 => [322, 32, 3],
            323 => [32, 3],
            33 => [3],
        ];

        $compiledActual = CategoryPermission::compileParentsBranchTree($tree);

        $I->assertEquals($compiledTree, $compiledActual);
        CategoryPermission::clearCompiledTree();
    }

    public function testCompileChildrenTree(\MyUnitTester $I)
    {
        CategoryPermission::clearCompiledTree();
        $tree = [
            1 => [11 => [111, 112]],
            2,
            3 => [31, 32 => [321, 322 => [3221, 3222], 323], 33],
        ];

        $compiledTree = [
            1 => [11, 111, 112],
            11 => [111, 112],
            111 => [],
            112 => [],
            2 => [],
            3 => [31, 32, 321, 322, 3221, 3222, 323, 33],
            31 => [],
            32 => [321, 322, 3221, 3222, 323],
            321 => [],
            322 => [3221, 3222],
            3221 => [],
            3222 => [],
            323 => [],
            33 => [],
        ];

        $compiledActual = CategoryPermission::compileChildrenBranchTree($tree);

        $I->assertEquals($compiledTree, $compiledActual);
        CategoryPermission::clearCompiledTree();
    }

    public function recursiveGrantRevoke(\MyUnitTester $I)
    {
        $category = $this->_category('n3');
        $category->assign('role1', CategoryPermission::COMMENT_ADD);

        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::PAGE_READ]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::COMMENT_ADD]
        );

        $category->revoke('role1', CategoryPermission::PAGE_READ);

        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::PAGE_READ]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::COMMENT_ADD]
        );

        //повторно проверяем удаление уже 3 уровнями
        $category->assign('role1', CategoryPermission::COMMENT_ADD);

        $category->revoke('role1', CategoryPermission::CATEGORY_VIEW);

        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::PAGE_READ]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::COMMENT_ADD]
        );
    }

    /**
     * @param string $id
     * @return Category
     */
    public function _category($id)
    {
        return $this->tester->grabFixture('category', $id);
    }

}
