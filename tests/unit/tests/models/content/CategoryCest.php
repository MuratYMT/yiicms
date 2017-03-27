<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 15:14
 */

namespace common\unit\test\models\content;

use yiicms\components\core\DateTime;
use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yiicms\tests\_data\fixtures\models\content\CategoryFixture;
use yiicms\tests\_data\fixtures\models\content\CategoryPermissionFixture;
use yiicms\tests\_data\fixtures\models\content\PageFixture;
use yiicms\tests\_data\fixtures\models\content\PageInCategoryFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use tests\unit\UnitCest;
use yii\db\Query;

/**
 * Class CategoryCest
 * @package tests\unit\test\models\web
 */
class CategoryCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'category' => CategoryFixture::className(),
            'permissions' => CategoryPermissionFixture::className(),
            'roles' => RoleFixture::className(),
            'pages' => PageFixture::className(),
            'pin' => PageInCategoryFixture::className(),
        ];
    }

    public function testAvailableCategories(\MyUnitTester $I)
    {
        $categories = Category::available(null, CategoryPermission::CATEGORY_VIEW);
        $I->assertCount(4, $categories);
        foreach ($categories as $category) {
            $I->assertTrue(in_array($category->categoryId, [300, 400, 500, 700], false));
        }

        $categories = Category::available($this->_category('n22'), CategoryPermission::CATEGORY_VIEW);
        $I->assertCount(1, $categories);
        foreach ($categories as $category) {
            $I->assertEquals(500, $category->categoryId);
        }

        $categories = Category::available($this->_category('n23'), CategoryPermission::CATEGORY_VIEW);
        $I->assertCount(0, $categories);

        $categories = Category::available($this->_category('n1'), CategoryPermission::CATEGORY_VIEW);
        $I->assertCount(0, $categories);
    }

    public function testDelete(\MyUnitTester $I)
    {
        $category = $this->_category('n22');
        //контрольная проверка
        $I->seeRecord(Category::className(), ['categoryId' => $category->categoryId]);
        $I->seeRecord(Category::className(), ['categoryId' => $this->_category('n221')->categoryId]);
        $I->seeRecord(Category::className(), ['categoryId' => $this->_category('n222')->categoryId]);

        $I->assertNotFalse($category->delete());
        $I->assertEquals(8, Category::find()->count());
        $I->dontSeeRecord(Category::className(), ['categoryId' => $category->categoryId]);
        $I->seeRecord(Category::className(), ['categoryId' => $this->_category('n221')->categoryId]);
        $I->seeRecord(Category::className(), ['categoryId' => $this->_category('n222')->categoryId]);
    }

    public function testDeleteRecursive(\MyUnitTester $I)
    {
        $category = $this->_category('n22');
        //контрольная проверка
        $I->seeRecord(Category::className(), ['categoryId' => $category->categoryId]);
        $I->seeRecord(Category::className(), ['categoryId' => $this->_category('n221')->categoryId]);
        $I->seeRecord(Category::className(), ['categoryId' => $this->_category('n222')->categoryId]);

        $I->assertEquals(3, $category->deleteRecursive());
        $I->assertEquals(6, (new Query())->from(Category::tableName())->count());
        $I->dontSeeRecord(Category::className(), ['categoryId' => $category->categoryId]);
        $I->dontSeeRecord(Category::className(), ['categoryId' => $this->_category('n221')->categoryId]);
        $I->dontSeeRecord(Category::className(), ['categoryId' => $this->_category('n222')->categoryId]);
    }

    public function testFindBySlug(\MyUnitTester $I)
    {
        $category = Category::findBySlug('category3');
        $I->assertNotNull($category);
        $I->assertEquals(800, $category->categoryId);
    }

    public function testAssignRevoke(\MyUnitTester $I)
    {
        \Yii::$app->db->createCommand()
            ->delete(CategoryPermission::tableName())
            ->execute();

        $category = $this->_category('n2');

        //назначаем ------------------------------
        $category->assign('role1', CategoryPermission::PAGE_CLOSE, false);

        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n22')->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n222')->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        //проверяем рекурсивность прав
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n22')->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n222')->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );

        //назначаем рекурсивно -----------------------------
        $category->assign('role1', CategoryPermission::PAGE_CLOSE, true);

        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n22')->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n222')->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        //проверяем рекурсивность прав
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n22')->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n222')->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );

        //отзываем -------------------------------
        $category->revoke('role1', CategoryPermission::CATEGORY_VIEW, false);

        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n22')->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n222')->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );

        //проверяем рекурсивность прав
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n22')->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n222')->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );

        //отзываем рекурсивно ---------------------------------
        $category->revoke('role1', CategoryPermission::CATEGORY_VIEW, true);

        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n22')->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n222')->categoryId, 'permission' => CategoryPermission::CATEGORY_VIEW]
        );

        //проверяем рекурсивность прав
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $category->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n22')->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['roleName' => 'role1', 'categoryId' => $this->_category('n222')->categoryId, 'permission' => CategoryPermission::PAGE_CLOSE]
        );
    }

    public function replaceChildrenPermission(\MyUnitTester $I)
    {
        CategoryPermission::clearCompiledTree();
        $I->dontSeeRecord(CategoryPermission::className(), ['categoryId' => 200, 'roleName' => 'role2']);
        $I->seeRecord(
            CategoryPermission::className(),
            ['categoryId' => 600, 'roleName' => 'role2', 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->seeRecord(CategoryPermission::className(), ['categoryId' => 600, 'roleName' => 'role2', 'permission' => CategoryPermission::COMMENT_ADD]);

        $category = $this->_category('n2');//200 категория
        $category->assign('role1', CategoryPermission::COMMENT_DELETE);

        $category->replaceChildrenPermission();

        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['categoryId' => 600, 'roleName' => 'role2', 'permission' => CategoryPermission::PAGE_CLOSE]
        );

        $I->seeRecord(
            CategoryPermission::className(),
            ['categoryId' => 200, 'roleName' => 'role1', 'permission' => CategoryPermission::COMMENT_DELETE]
        );

        $I->seeRecord(
            CategoryPermission::className(),
            ['categoryId' => 400, 'roleName' => 'role1', 'permission' => CategoryPermission::COMMENT_DELETE]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['categoryId' => 600, 'roleName' => 'role1', 'permission' => CategoryPermission::COMMENT_DELETE]
        );

        $I->seeRecord(
            CategoryPermission::className(),
            ['categoryId' => 200, 'roleName' => 'role1', 'permission' => CategoryPermission::CATEGORY_VIEW]
        );

        $I->seeRecord(
            CategoryPermission::className(),
            ['categoryId' => 400, 'roleName' => 'role1', 'permission' => CategoryPermission::CATEGORY_VIEW]
        );
        $I->seeRecord(
            CategoryPermission::className(),
            ['categoryId' => 600, 'roleName' => 'role1', 'permission' => CategoryPermission::CATEGORY_VIEW]
        );

        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['categoryId' => 200, 'roleName' => 'role2', 'permission' => CategoryPermission::PAGE_CLOSE]
        );

        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['categoryId' => 400, 'roleName' => 'role2', 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        $I->dontSeeRecord(
            CategoryPermission::className(),
            ['categoryId' => 600, 'roleName' => 'role2', 'permission' => CategoryPermission::PAGE_CLOSE]
        );
        CategoryPermission::clearCompiledTree();
    }

    public function testCan(\MyUnitTester $I)
    {
        $category = Category::findOne(['categoryId' => 600]);
        $I->assertNotNull($category);

        $I->assertTrue($category->can(CategoryPermission::COMMENT_ADD));
        $I->assertFalse($category->can(CategoryPermission::CATEGORY_VIEW));

        //рекурсивные роли
        $auth = \Yii::$app->authManager;
        $auth->assign($auth->getRole('role1'), 220);

        $category->assign('role111', CategoryPermission::CATEGORY_VIEW);
        $I->assertTrue($category->can(CategoryPermission::CATEGORY_VIEW));
    }

    public function testCreate(\MyUnitTester $I)
    {
        $category = new Category();
        $category->scenario = Category::SC_EDIT;
        $category->load([
            'zz' => [
                'parentId' => 0,
                'title' => 'zzzz',
                'description' => 'xxxxxxxxxxx',
                'slug' => 'wertyq',
                'lang' => 'ru',
                'weight' => 0,
                'keywords' => 'test test',
            ],
        ], 'zz');
        $I->assertTrue($category->save());

        $I->assertEquals($category->categoryId, $category->mPath);
        $I->assertEquals(1, $category->levelNod);
        $I->assertEquals(DateTime::runTime(), $category->createdAt);
        $I->assertNull($category->parent);
        $I->assertCount(0, $category->children);

        $category = new Category();
        $category->scenario = Category::SC_EDIT;
        $category->load([
            'zz' => [
                'parentId' => 800,
                'title' => 'zzzz',
                'description' => 'xxxxxxxxxxx',
                'slug' => 'wertyq2',
                'lang' => 'ru',
                'weight' => 0,
                'keywords' => 'test test',
            ],
        ], 'zz');
        $I->assertTrue($category->save());

        $I->assertEquals('800^' . $category->categoryId, $category->mPath);
        $I->assertEquals(2, $category->levelNod);
        $I->assertNotNull($category->parent);
        $I->assertEquals(800, $category->parent->categoryId);
        $I->assertCount(0, $category->children);

        $I->assertCount(1, $category->parent->children);
    }

    public function testBranches(\MyUnitTester $I)
    {
        $category = $this->_category('n2');
        $children = $category->childrenBranch;

        $I->assertCount(5, $children);
        foreach ($children as $child) {
            $I->assertTrue(in_array($child->categoryId, [300, 400, 500, 600, 700], false));
        }

        $category = $this->_category('n221');
        $parents = $category->parentsBranch;
        $I->assertCount(2, $parents);
        $res = [];
        foreach ($parents as $parent) {
            $res[] = $parent->categoryId;
        }
        $I->assertEquals([200, 400], $res);
    }

    public function testPermissionLink(\MyUnitTester $I)
    {
        $category = Category::findOne(['categoryId' => 700]);
        $I->assertNotNull($category);
        $perms = $category->permissions;

        $I->assertCount(2, $perms);
        foreach ($perms as $perm) {
            $I->assertTrue(in_array($perm->permission, [CategoryPermission::COMMENT_ADD, CategoryPermission::CATEGORY_VIEW], true));
        }

        $category = Category::findOne(['categoryId' => 150]);
        $I->assertNotNull($category);
        $perms = $category->permissions;
        $I->assertCount(4, $perms);

        $perm = [];
        foreach ($perms as $item) {
            $perm[] = $item->permission;
        }
        $I->assertContains(CategoryPermission::CATEGORY_VIEW, $perm);
        $I->assertContains(CategoryPermission::PAGE_EDIT_OWN, $perm);
        $I->assertContains(CategoryPermission::PAGE_EDIT, $perm);
    }

    public function testPagesBranch(\MyUnitTester $I)
    {
        $category = Category::findOne(['categoryId' => 400]);
        $I->assertNotNull($category);

        $pages = $category->pagesBranch;
        $I->assertCount(18, $pages);
        foreach ($pages as $page) {
            $I->assertTrue(
                in_array(
                    $page->pageId,
                    [11100, 10100, 1100, 1200, 1300, 1400, 1500, 1600, 1700, 1800, 100, 200, 300, 400, 500, 600, 700, 800,],
                    false
                )
            );
        }
        $pages = $category->pages;
        $I->assertCount(16, $pages);
        foreach ($pages as $page) {
            $I->assertTrue(
                in_array(
                    $page->pageId,
                    [1100, 1200, 1300, 1400, 1500, 1600, 1700, 1800, 100, 200, 300, 400, 500, 600, 700, 800,],
                    false
                )
            );
        }
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
