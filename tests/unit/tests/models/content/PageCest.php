<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 27.05.2016
 * Time: 11:38
 */

namespace common\unit\test\models\content;

use yiicms\components\core\ArrayHelper;
use yiicms\components\core\DateTime;
use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yiicms\models\content\Page;
use yiicms\models\content\PageInTag;
use yiicms\models\content\Tag;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Users;
use yiicms\tests\_data\fixtures\models\content\CategoryFixture;
use yiicms\tests\_data\fixtures\models\content\CategoryPermissionFixture;
use yiicms\tests\_data\fixtures\models\content\PageFixture;
use yiicms\tests\_data\fixtures\models\content\PageInCategoryFixture;
use yiicms\tests\_data\fixtures\models\content\PageInTagFixture;
use yiicms\tests\_data\fixtures\models\content\TagFixture;
use yiicms\tests\_data\fixtures\models\core\LoadedFilesFixture;
use tests\unit\UnitCest;

/**
 * Class PageCest
 * @package tests\unit\test\models\web
 */
class PageCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'loadedFiles' => LoadedFilesFixture::className(),
            'category' => CategoryFixture::className(),
            'tags' => TagFixture::className(),
            'pages' => PageFixture::className(),
            'pin' => PageInCategoryFixture::className(),
            'pit' => PageInTagFixture::className(),
            'perm' => CategoryPermissionFixture::className(),
        ];
    }

    public function testPagesForUser(\MyUnitTester $I)
    {
        $category = $this->_category('n21');

        $pages = Page::visiblePagesForUser($category, false)->all();
        $I->assertCount(0, $pages);

        $category = $this->_category('n221');
        $pages = Page::visiblePagesForUser($category, true)->all();
        $I->assertCount(1, $pages);

        $category = $this->_category('n22');
        $pages = Page::visiblePagesForUser($category, true)->all();
        $I->assertCount(5, $pages);
        $pages = Page::visiblePagesForUser($category, false)->all();
        $I->assertCount(4, $pages);
    }

    public function addDeleteEditPermission(\MyUnitTester $I)
    {
        $owner = Users::findOne(220);
        $page = new Page();
        $page->scenario = Page::SC_EDIT;
        $page->owner = $owner;
        $page->title = 'title 1';
        $page->pageText = 'text text text text';
        $page->categoriesIds = [300, 700];

        //в 700 категории нет прав на добавление страниц
        $I->assertFalse($page->save());
        $I->assertTrue($page->hasErrors('categoriesIds'));

        $page->categoriesIds = [300];
        $I->assertTrue($page->save());

        $I->assertEquals(DateTime::runTime(), $page->createdAt);
        $I->assertNull($page->lastEditedAt);

        $page->categoriesIds = [300, 800];
        $I->assertTrue($page->save());

        $I->assertEquals(DateTime::runTime(), $page->lastEditedAt);
        $page->categoriesIds = [800];

        //удалять из 300 категории нет прав
        $I->assertFalse($page->save());
        Category::findOne(300)->assign('role2', CategoryPermission::PAGE_DELETE);

        //еще нет прав на редактирование в 800 категории
        $I->assertFalse($page->save());
        Category::findOne(800)->assign('role2', CategoryPermission::PAGE_EDIT);

        $I->assertTrue($page->save());
    }

    public function testImagesIfDeletePage(\MyUnitTester $I)
    {
        $page = $this->_pages('p1');

        $page->imagesIds = ['abcdefg', 'hijklmn'];
        $I->assertTrue($page->save());

        $image1 = $this->_images('f1');
        $image2 = $this->_images('f2');

        $I->assertNotFalse($page->delete());

        $I->assertFileNotExists(\Yii::getAlias('@uploadFolder/' . $image1->path));
        $I->assertFileNotExists(\Yii::getAlias('@uploadFolder/' . $image2->path));
        $I->assertNull(LoadedFiles::findOne(['id' => $image1->id]));
        $I->assertNull(LoadedFiles::findOne(['id' => $image2->id]));
    }

    public function testAddView(\MyUnitTester $I)
    {
        $page = $this->_pages('p1');
        $page->addView();

        $page = Page::findOne(100);
        $I->assertNotNull($page);
        $I->assertEquals(1, $page->viewCount);

        $page->addView(10);

        $page = Page::findOne(100);
        $I->assertNotNull($page);
        $I->assertEquals(11, $page->viewCount);
    }

    public function testTagString(\MyUnitTester $I)
    {
        $page = $this->_pages('p1');
        $page->tags = [];
        $I->assertTrue($page->save());
        $I->assertEquals(0, PageInTag::find()->where(['pageId' => $page->pageId])->count());

        $page->tagsString = 'Строка 1, Строка 2, Строка 3';
        $I->assertTrue($page->save());
        $I->assertEquals(3, PageInTag::find()->where(['pageId' => $page->pageId])->count());

        $tagString = $page->tagsString;

        $I->assertContains('Строка 1', $tagString);
        $I->assertContains('Строка 2', $tagString);
        $I->assertContains('Строка 3', $tagString);

        $I->assertCount(3, $page->tags);
        $tags = ArrayHelper::getColumn($page->tags, 'title');

        $I->assertContains('Строка 1', $tags);
        $I->assertContains('Строка 2', $tags);
        $I->assertContains('Строка 3', $tags);

        //добавление тега к странице
        $page->tagsString = 'Строка 1, Строка 2, Строка 3, Строка 4';
        $I->assertTrue($page->save());
        $I->assertEquals(4, PageInTag::find()->where(['pageId' => $page->pageId])->count());

        $tagString = $page->tagsString;

        $I->assertContains('Строка 1', $tagString);
        $I->assertContains('Строка 2', $tagString);
        $I->assertContains('Строка 3', $tagString);
        $I->assertContains('Строка 4', $tagString);

        $I->assertCount(4, $page->tags);
        $tags = ArrayHelper::getColumn($page->tags, 'title');

        $I->assertContains('Строка 1', $tags);
        $I->assertContains('Строка 2', $tags);
        $I->assertContains('Строка 3', $tags);
        $I->assertContains('Строка 4', $tags);

        //удаление тега у страницы
        $page->tagsString = 'Строка 2, Строка 3, Строка 4';
        $I->assertTrue($page->save());
        $I->assertEquals(3, PageInTag::find()->where(['pageId' => $page->pageId])->count());

        $tagString = $page->tagsString;

        $I->assertContains('Строка 2', $tagString);
        $I->assertContains('Строка 3', $tagString);
        $I->assertContains('Строка 4', $tagString);

        $I->assertCount(3, $page->tags);
        $tags = ArrayHelper::getColumn($page->tags, 'title');

        $I->assertContains('Строка 2', $tags);
        $I->assertContains('Строка 3', $tags);
        $I->assertContains('Строка 4', $tags);

        //проверка наличия тега в базе данных
        $I->seeRecord(Tag::className(), ['title' => 'Строка 1']);
        $I->seeRecord(Tag::className(), ['title' => 'Строка 2']);
        $I->seeRecord(Tag::className(), ['title' => 'Строка 3']);
        $I->seeRecord(Tag::className(), ['title' => 'Строка 4']);
    }

    public function testCan(\MyUnitTester $I)
    {
        $page = Page::findOne(100);
        $I->assertTrue($page->can(CategoryPermission::CATEGORY_VIEW));
        $I->assertFalse($page->can(CategoryPermission::COMMENT_ADD));

        $page = Page::findOne(11100);
        $I->assertFalse($page->can(CategoryPermission::CATEGORY_VIEW));

        //имеет право редактировать
        $page = Page::findOne(200);
        $I->assertTrue($page->can(CategoryPermission::PAGE_EDIT));

        //имеет право редактировать как автор
        \Yii::$app->user->login(Users::findIdentity(-1));
        $I->assertTrue($page->can(CategoryPermission::PAGE_EDIT));

        $page = Page::findOne(100);
        $I->assertFalse($page->can(CategoryPermission::PAGE_EDIT));

        \Yii::$app->user->login(Users::findIdentity(220));
        $I->assertTrue($page->can(CategoryPermission::PAGE_EDIT));
    }

    public function testCanRecursiveRole(\MyUnitTester $I)
    {
        $owner = Users::findOne(220);
        $page = new Page();
        $page->scenario = Page::SC_EDIT;
        $page->owner = $owner;
        $page->title = 'title 1';
        $page->pageText = 'text text text text';
        $page->categoriesIds = [700];

        //в 700 категории нет прав на добавление страниц
        $I->assertFalse($page->save());
        $I->assertTrue($page->hasErrors('categoriesIds'));

        //заодно проверяем рекурсивность прав в ролях
        Category::findOne(700)->assign('role111', CategoryPermission::PAGE_ADD);
        \Yii::$app->authManager->assign(\Yii::$app->authManager->getRole('role1'), 220);

        $I->assertTrue($page->save());
    }

    public function testIsPublished(\MyUnitTester $I)
    {
        $page = $this->_pages('p1');
        $I->assertTrue($page->isPublished);
        $page = $this->_pages('p2');
        $I->assertFalse($page->isPublished);
        $page = $this->_pages('p3');
        $I->assertFalse($page->isPublished);
        $page = $this->_pages('p4');
        $I->assertTrue($page->isPublished);
        $page = $this->_pages('p5');
        $I->assertFalse($page->isPublished);
        $page = $this->_pages('p6');
        $I->assertTrue($page->isPublished);
        $page = $this->_pages('p7');
        $I->assertFalse($page->isPublished);
        $page = $this->_pages('p8');
        $I->assertTrue($page->isPublished);
        $page = $this->_pages('p11');
        $I->assertFalse($page->isPublished);
    }

    public function testSlugFull(\MyUnitTester $I)
    {
        $page = $this->_pages('p1');
        $I->assertEquals('100-page1', $page->slugFull);
    }

    public function testImagesIds(\MyUnitTester $I)
    {
        $page = $this->_pages('p1');
        $page->scenario = Page::SC_EDIT;

        $page->imagesIds = ['abcdefg', 'hijklmn'];
        $I->assertTrue($page->save());
        $images = $page->images;
        $I->assertCount(2, $images);

        $img = ArrayHelper::getColumn($images, 'id');

        $I->assertContains('abcdefg', $img);
        $I->assertContains('hijklmn', $img);

        $page->imagesIds = ['abcdefg', 'hijklmn', 'oprstqy'];
        $I->assertTrue($page->save());
        $images = $page->images;
        $I->assertCount(3, $images);
        $img = ArrayHelper::getColumn($images, 'id');

        $I->assertContains('abcdefg', $img);
        $I->assertContains('hijklmn', $img);
        $I->assertContains('oprstqy', $img);

        $page->imagesIds = ['hijklmn', 'oprstqy'];
        $I->assertTrue($page->save());
        $images = $page->images;
        $I->assertCount(2, $images);
        $img = ArrayHelper::getColumn($images, 'id');

        $I->assertContains('hijklmn', $img);
        $I->assertContains('oprstqy', $img);
    }

    /**
     * @param string $id
     * @return Page
     */
    public function _pages($id)
    {
        return $this->tester->grabFixture('pages', $id);
    }

    /**
     * @param string $id
     * @return LoadedFiles
     */
    public function _images($id)
    {
        return $this->tester->grabFixture('loadedFiles', $id);
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
