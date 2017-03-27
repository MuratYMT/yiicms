<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 27.07.2016
 * Time: 15:56
 */

namespace tests\acceptance\modules\admin;

use Facebook\WebDriver\WebDriverKeys;
use tests\acceptance\AcceptanceCest;
use yiicms\models\content\CategoryPermission;
use yiicms\tests\_data\fixtures\models\content\CategoryPermissionFixture;
use yiicms\tests\_data\fixtures\models\content\PageFixture;
use yiicms\tests\_data\fixtures\models\content\PageInCategoryFixture;
use yiicms\tests\_data\fixtures\models\content\PageInTagFixture;

class PagesCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            PageFixture::className(),
            PageInCategoryFixture::className(),
            PageInTagFixture::className(),
            CategoryPermissionFixture::className(),
        ];
    }

    public function testIndex(\AcceptanceTester $I)
    {
        self::_openPagesPage($I);

        $I->see('page1', 'table>tbody>tr>td');
        $I->see('page2', 'table>tbody>tr>td');
        $I->see('page3', 'table>tbody>tr>td');
        $I->see('page4', 'table>tbody>tr>td');
        $I->see('page5', 'table>tbody>tr>td');
        $I->see('page6', 'table>tbody>tr>td');
        $I->see('page7', 'table>tbody>tr>td');
        $I->see('page8', 'table>tbody>tr>td');
        $I->see('page11', 'table>tbody>tr>td');
        $I->see('page12', 'table>tbody>tr>td');
        $I->see('page13', 'table>tbody>tr>td');
        $I->see('page14', 'table>tbody>tr>td');
        $I->see('page15', 'table>tbody>tr>td');
        $I->see('page16', 'table>tbody>tr>td');
        $I->see('page17', 'table>tbody>tr>td');
        $I->see('page18', 'table>tbody>tr>td');
        $I->see('page101', 'table>tbody>tr>td');
        $I->see('page111', 'table>tbody>tr>td');
    }

    public function testCreatePage(\AcceptanceTester $I)
    {
        self::login($I);

        CategoryCest::_grantPermissionToRoleForCategory($I, 'category3', 'role2', CategoryPermission::PAGE_EDIT);

        self::_openPagesPage($I, false);

        $I->click('Создать страницу');
        $I->see('Создать страницу');

        $I->fillField('#pageeditform-title', 'test page title');
        $I->fillField('#pageeditform-announce', 'test page announce');

        $I->switchToIFrame('pageeditform-pagetext_ifr');
        $I->executeJS('document.getElementById("tinymce").innerHTML = "<p>test page text</p>";');
        $I->switchToIFrame();

        $I->click('a[href="#categories"]');
        //$I->executeJS('$("#pageeditform-categoriesids800").iCheck("check");');
        $I->checkOption('#pageeditform-categoriesids800');
        $I->click('a[href="#publication"]');
        $I->click('button[value="save"]');
        $I->see('Страница создана', '.toast-message');
        $I->click('Сохранить и закрыть');
        $I->see('Страница изменена', '.toast-message');

        self::_selectPage($I, 'test page title');
        $I->see('test page title', 'table>tbody>tr>td');
        $I->see('SXSXS', 'table>tbody>tr>td');
        $I->see('ru', 'table>tbody>tr>td');
    }

    public function testEditPage(\AcceptanceTester $I)
    {
        self::login($I);

        CategoryCest::_grantPermissionToRoleForCategory($I, 'category22', 'role2', CategoryPermission::PAGE_EDIT);
        CategoryCest::_grantPermissionToRoleForCategory($I, 'category22', 'role2', CategoryPermission::CATEGORY_VIEW);

        self::_openPagesPage($I, false);

        self::_selectPage($I, 'page18');

        $I->click('a[title="Изменить страницу"]');
        $I->see('Изменить страницу');

        $I->fillField('#pageeditform-title', 'page1818');
        $I->click('Сохранить и закрыть');
        $I->see('Страница изменена');

        self::_openPagesPage($I, false);
        self::_selectPage($I, 'page1818');
        $I->see('page1818', 'table>tbody>tr>td');
    }

    // ------------------------------------------------------------------------------------

    public static function _selectPage(\AcceptanceTester $I, $pageTitle)
    {
        $I->fillField('input[name="PagesSearch[title]"]', $pageTitle);
        $I->pressKey('input[name="PagesSearch[title]"]', WebDriverKeys::ENTER);
    }

    public static function _openPagesPage(\AcceptanceTester $I, $auth = true)
    {
        if ($auth) {
            self::login($I);
        }
        $I->amOnPage('/admin/pages');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Страницы сайта', 'h1');
    }
}
