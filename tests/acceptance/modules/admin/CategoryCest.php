<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.06.2016
 * Time: 9:49
 */

namespace tests\acceptance\modules\admin;

use admin\tests\AcceptanceTester;
use Facebook\WebDriver\WebDriverKeys;
use tests\acceptance\AcceptanceCest;
use yiicms\models\content\CategoryPermission;
use yiicms\tests\_data\fixtures\models\content\CategoryFixture;
use yiicms\tests\_data\fixtures\models\content\CategoryPermissionFixture;

class CategoryCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            CategoryFixture::className(),
            CategoryPermissionFixture::className(),
        ];
    }

    public function testIndex(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);

        $I->see('category1', 'div[style*="margin-left: 0px"]');
        $I->see('category11', 'div[style*="margin-left: 30px"]');
        $I->see('category221', 'div[style*="margin-left: 60px"]');
        $I->see('category3', 'div[style*="margin-left: 0px"]');
    }

    public function testAddRootCategory(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);
        $I->click('Создать корневую категорию');

        $I->see('Добавить категорию', 'h1');
        $I->seeOptionIsSelected('select[name="CategoryEditForm[parentId]"]', 'Без родительской категории');

        $I->fillField('input[name="CategoryEditForm[title]"]', 'category test');
        $I->fillField('input[name="CategoryEditForm[description]"]', 'test description');
        $I->fillField('input[name="CategoryEditForm[weight]"]', '10');
        $I->fillField('textarea[name="CategoryEditForm[keywords]"]', 'test category 1, test category2');

        $I->click('Сохранить');

        $I->see('Категория создана', '.toast-message');

        self::_selectCategory($I, 'category test');

        $I->see('category test', 'table>tbody>tr>td');
    }

    public function testEditCategory(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);

        self::_selectCategory($I, 'category3');

        $I->clickPopupMenu('Изменить');

        $I->see('Изменить категорию "category3"', 'h1');

        $I->seeOptionIsSelected('select[name="CategoryEditForm[parentId]"]', 'Без родительской категории');
        $I->seeOptionIsSelected('select[name="CategoryEditForm[lang]"]', 'ru');
        $I->seeInField('input[name="CategoryEditForm[title]"]', 'category3');
        $I->seeInField('input[name="CategoryEditForm[description]"]', 'category3 description');
        $I->seeInField('input[name="CategoryEditForm[weight]"]', '0');
        $I->seeInField('textarea[name="CategoryEditForm[keywords]"]', 'category3 keywords');

        $I->fillField('input[name="CategoryEditForm[title]"]', 'category44');
        $I->fillField('input[name="CategoryEditForm[description]"]', 'category44 description');
        $I->fillField('input[name="CategoryEditForm[weight]"]', '10');
        $I->fillField('textarea[name="CategoryEditForm[keywords]"]', 'category44 keywords');
        $I->click('Сохранить');

        $I->see('Категория "category44" изменена', '.toast-message');

        self::_openCategoriesPage($I, false);
        self::_selectCategory($I, 'category44');
        $I->see('category44', 'table>tbody>tr>td');
        $I->see('10', 'table>tbody>tr>td');
        $I->see('category44 keywords', 'table>tbody>tr>td');
    }

    public function testChangeParentCategory(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);

        self::_selectCategory($I, 'category3');

        $I->see('category3', 'div[style*="margin-left: 0px"]');

        $I->clickPopupMenu('Изменить');

        $I->selectOption('select[name="CategoryEditForm[parentId]"]', 'category1');
        $I->click('Сохранить');

        $I->see('Категория "category3" изменена', '.toast-message');
        $I->see('category3', 'div[style*="margin-left: 30px"]');

        $I->clickPopupMenu('Изменить');

        $I->selectOption('select[name="CategoryEditForm[parentId]"]', 'Без родительской категории');
        $I->click('Сохранить');

        $I->see('Категория "category3" изменена', '.toast-message');
        $I->see('category3', 'div[style*="margin-left: 0px"]');
    }

    public function testAddChild(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);
        self::_selectCategory($I, 'category3');

        $I->clickPopupMenu('Добавить дочернюю');

        $I->see('Добавить категорию', 'h1');

        $I->seeOptionIsSelected('select[name="CategoryEditForm[parentId]"]', 'category3');

        $I->fillField('input[name="CategoryEditForm[title]"]', 'category44');
        $I->fillField('input[name="CategoryEditForm[description]"]', 'category44 description');
        $I->fillField('input[name="CategoryEditForm[weight]"]', '10');
        $I->fillField('textarea[name="CategoryEditForm[keywords]"]', 'category44 keywords');
        $I->click('Сохранить');

        $I->see('Категория создана', '.toast-message');

        self::_openCategoriesPage($I, false);
        self::_selectCategory($I, 'category44');

        $I->see('category44', 'table>tbody>tr>td');
        $I->see('category44', 'div[style*="margin-left: 30px"]');
    }

    public function testDel(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);
        self::_selectCategory($I, 'category22');

        $I->see('category22', 'div[style*="margin-left: 30px"]');
        $I->see('category221', 'div[style*="margin-left: 60px"]');

        $I->clickPopupMenu('Удалить', null, null, 1);

        $I->see('Удалить категории?');
        $I->clickDlgConfirm();

        $I->see('Категория "category22" удалена', '.toast-message');
        self::_openCategoriesPage($I, false);

        $I->see('category221', 'div[style*="margin-left: 30px"]');
    }

    public function testDelRecursive(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);
        self::_selectCategory($I, 'category2');

        $I->see('category2', 'div[style*="margin-left: 0px"]');
        $I->see('category22', 'div[style*="margin-left: 30px"]');
        $I->see('category221', 'div[style*="margin-left: 60px"]');

        $I->clickPopupMenu('Удалить с дочерними', null, null, 1);

        $I->see('Удалить категорию и все ее дочерние подкатегории?');
        $I->clickDlgConfirm();

        $I->see('Категория "category2" удалена', '.toast-message');
        self::_openCategoriesPage($I, false);

        $I->dontSee('category2', 'table>tbody>tr>td');
        $I->dontSee('category22', 'table>tbody>tr>td');
        $I->dontSee('category221', 'table>tbody>tr>td');
    }

    public function testPermission(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);
        self::_selectCategory($I, 'category3');

        $I->clickPopupMenu('Разрешения');

        $I->see('Разрешения категории "category3"', 'h1');

        self::_selectRole($I, 'role2');

        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[3][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[4][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[5][.//span[contains(@class, \'glyphicon-ok\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[6][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[7][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[8][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[9][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[10][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[11][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[12][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[13][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[14][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[15][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[16][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[17][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[18][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[19][.//span[contains(@class, \'glyphicon-remove\')]]');
    }

    public function testChangePermission(\AcceptanceTester $I)
    {
        self::_openCategoriesPage($I);
        self::_selectCategory($I, 'category3');
        $I->clickPopupMenu('Разрешения');
        self::_selectRole($I, 'role2');

        $I->click('a[title="Изменить разрешения для этой роли в этой категории"]');
        $I->see('Редактировать разрешения категории "category3" для роли "role2"', 'h1');

        $allowed = [CategoryPermission::PAGE_ADD];

        foreach (CategoryPermission::$permissions as $permission) {
            if (in_array($permission, $allowed, true)) {
                $I->seeCheckboxIsChecked('input[id="categorypermissioneditform-' . strtolower($permission) . '"]');
            } else {
                $I->cantSeeCheckboxIsChecked('input[id="categorypermissioneditform-' . strtolower($permission) . '"]');
            }
        }

        $I->checkOption('input[id="categorypermissioneditform-' . strtolower(CategoryPermission::CATEGORY_VIEW) . '"]');
        $I->checkOption('input[id="categorypermissioneditform-' . strtolower(CategoryPermission::PAGE_EDIT) . '"]');

        $I->uncheckOption('input[id="categorypermissioneditform-' . strtolower(CategoryPermission::PAGE_ADD) . '"]');

        $I->click('Сохранить');
        $I->see('Разрешения изменены', '.toast-message');

        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[3][.//span[contains(@class, \'glyphicon-ok\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[4][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[5][.//span[contains(@class, \'glyphicon-ok\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[6][.//span[contains(@class, \'glyphicon-ok\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[7][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[8][.//span[contains(@class, \'glyphicon-ok\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[9][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[10][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[11][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[12][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[13][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[14][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[15][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[16][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[17][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[18][.//span[contains(@class, \'glyphicon-remove\')]]');
        $I->seeElement('.//*[@id=\'category-permission-grid-container\']/table/tbody/tr[1]/td[19][.//span[contains(@class, \'glyphicon-remove\')]]');
    }

    // -------------------------------------------------------

    /**
     * устанавливает разрешение на указанную категорию для указанного пользователя
     * @param AcceptanceTester $I
     * @param string $category имя узла
     * @param string $role название роли
     * @param string $permission устанавливаемое разрешение
     */
    public static function _grantPermissionToRoleForCategory(\AcceptanceTester $I, $category, $role, $permission)
    {
        self::_openCategoriesPage($I, false);
        self::_selectCategory($I, $category);
        $I->clickPopupMenu('Разрешения');
        self::_selectRole($I, $role);
        $I->click('a[title="Изменить разрешения для этой роли в этой категории"]');

        //$I->executeJS('$("#registrationform-ruleread").iCheck("check");');
        $I->checkOption('input[id="categorypermissioneditform-' . strtolower($permission) . '"]');
        $I->click('Сохранить');
    }

    /**
     * отменяет разрешение на указанную категорию для указанного пользователя
     * @param AcceptanceTester $I
     * @param string $category имя узла
     * @param string $role название роли
     * @param string $permission устанавливаемое разрешение
     */
    public static function _revokePermissionFromRoleForCategory(\AcceptanceTester $I, $category, $role, $permission)
    {
        self::_openCategoriesPage($I, false);
        self::_selectCategory($I, $category);
        $I->clickPopupMenu('Разрешения');
        self::_selectRole($I, $role);
        $I->click('a[title="Изменить разрешения для этой роли в этой категории"]');

        $I->executeJS('$(\'input[id="categorypermissioneditform-' . strtolower($permission) . '"]\').iCheck("uncheck");');
        //$I->uncheckOption('input[id="categorypermissioneditform-' . strtolower($permission) . '"]');
        $I->click('Сохранить');
    }

    public static function _selectRole(\AcceptanceTester $I, $role)
    {
        $I->fillField('input[name="CategoryPermissionSearch[roleName]"]', $role);
        $I->pressKey('input[name="CategoryPermissionSearch[roleName]"]', WebDriverKeys::ENTER);
    }

    public static function _selectCategory(\AcceptanceTester $I, $category)
    {
        $I->fillField('input[name="CategoriesSearch[title]"]', $category);
        $I->pressKey('input[name="CategoriesSearch[title]"]', WebDriverKeys::ENTER);
    }

    public static function _openCategoriesPage(\AcceptanceTester $I, $auth = true)
    {
        if ($auth) {
            self::login($I);
        }
        $I->amOnPage('/admin/categories');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Категории', 'h1');
    }
}
