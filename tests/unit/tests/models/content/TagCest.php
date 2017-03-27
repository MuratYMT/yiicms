<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 26.05.2016
 * Time: 12:26
 */

namespace common\unit\test\models\content;

use yiicms\models\content\Page;
use yiicms\models\content\Tag;
use yiicms\tests\_data\fixtures\models\content\CategoryPermissionFixture;
use yiicms\tests\_data\fixtures\models\content\PageFixture;
use yiicms\tests\_data\fixtures\models\content\PageInCategoryFixture;
use yiicms\tests\_data\fixtures\models\content\PageInTagFixture;
use yiicms\tests\_data\fixtures\models\content\TagFixture;
use tests\unit\UnitCest;
use yii\db\Query;
use yii\helpers\Inflector;

/**
 * Class TagCest
 * @package tests\unit\test\models\web
 *
 */
class TagCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'tags' => TagFixture::className(),
            'pit' => PageInTagFixture::className(),
            'pages' => PageFixture::className(),
            'pin' => PageInCategoryFixture::className(),
            'perm' => CategoryPermissionFixture::className(),
        ];
    }

    public function testStringToTags(\MyUnitTester $I)
    {
        $tags = Tag::stringToTags('Тег 3, Путин, Мюнхен');
        $I->assertCount(3, $tags);
        $I->assertEquals(5, Tag::find()->count());
        $I->seeRecord(Tag::className(), ['title' => 'Тег 3']);
        $I->seeRecord(Tag::className(), ['title' => 'Путин']);
        $I->seeRecord(Tag::className(), ['title' => 'Мюнхен']);
    }

    public function testPagesForUser(\MyUnitTester $I)
    {
        $tag = Tag::findOne(100);
        /** @var \yiicms\models\content\Page[] $pages */
        $pages = $tag->visiblePagesForUser()->all();

        $I->assertCount(2, $pages);
        foreach ($pages as $page) {
            $I->assertTrue(in_array($page->pageId, [100, 10100], false));
        }

        $tag = Tag::findOne(200);
        /** @var \yiicms\models\content\Page[] $pages */
        $pages = $tag->visiblePagesForUser()->all();

        $I->assertCount(1, $pages);
        foreach ($pages as $page) {
            $I->assertEquals(100, $page->pageId);
        }
    }

    public function testTagEdit(\MyUnitTester $I)
    {
        $tag = Tag::findOne(100);
        $tag->title = 'edit34';
        $tag->save();

        $tag = Tag::findOne(100);
        $I->assertEquals('edit34', $tag->slug);

        $pages = $tag->pages;
        $I->assertCount(3, $pages);

        foreach ($pages as $page) {
            foreach ($page->tags as $tag) {
                if ($tag->tagId === 100) {
                    $I->assertEquals('edit34', $tag->title);
                    $I->assertEquals('edit34', $tag->slug);
                }
            }
        }
    }

    public function testTagDelete(\MyUnitTester $I)
    {
        $tag = Tag::findOne(100);
        $tag->delete();
        $count = (new Query())
            ->from(Tag::tableName())
            ->count();

        $I->assertEquals(2, $count);

        $I->assertNull(Tag::findOne(100));
        /** @var \yiicms\models\content\Page[] $pages */
        $pages = Page::findAll([11100, 10100, 100]);

        $I->assertCount(3, $pages);

        foreach ($pages as $page) {
            $tags = $page->tags;
            $res = [];
            foreach ($tags as $tag) {
                $res[] = $tag->tagId;
            }

            $I->assertFalse(in_array(100, $res, false));
        }
    }

    public function testFindBySlug(\MyUnitTester $I)
    {
        $tag = Tag::findBySlug(Inflector::slug('Тег 1'));
        $I->assertNotNull($tag);
        $I->assertEquals(100, $tag->tagId);
    }

    /**
     * @param string $id
     * @return \yiicms\models\content\Page
     */
    public function _pages($id)
    {
        return $this->tester->grabFixture('pages', $id);
    }

    /**
     * @param string $id
     * @return Tag
     */
    public function _tags($id)
    {
        return $this->tester->grabFixture('tags', $id);
    }
}
