<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 8:38
 */

namespace common\unit\test\models\web;

use yiicms\models\core\LoadedFiles;
use yiicms\models\core\VFiles;
use yiicms\models\core\VFolders;
use yiicms\tests\_data\fixtures\models\core\LoadedFilesFixture;
use yiicms\tests\_data\fixtures\models\core\VFoldersFixture;
use tests\unit\UnitCest;
use yii\db\Query;

/**
 * Class VFoldersCest
 * @package tests\unit\test\models\web
 *
 * @method VFolders folders($id)
 * @method VFiles vfiles($id)
 * @method LoadedFiles loadedFiles($id)
 */
class VFoldersCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'folders' => VFoldersFixture::className(),
            'loadedFiles' => LoadedFilesFixture::className(),
        ];
    }

    public function testParentRule(\MyUnitTester $I)
    {
        $folder = new VFolders();
        $folder->scenario = VFolders::SC_INSERT;
        $folder->parentId = 0;
        $folder->userId = 220;
        $folder->title = 'ertyu';

        $folder->validate(['parentId']);
        $I->assertTrue($folder->hasErrors('parentId'));

        $folder->parentId = $this->_folders('f1')->folderId;

        $folder->validate();
        $I->assertFalse($folder->hasErrors('parentId'));

        $folder->parentId = -1;

        $folder->validate();
        $I->assertTrue($folder->hasErrors('parentId'));
    }

    public function testSave(\MyUnitTester $I)
    {
        $folder = new VFolders();
        $folder->scenario = VFolders::SC_INSERT;
        $folder->load(['zz' => ['title' => 'test1']], 'zz');
        $folder->parentId = $this->_folders('f122')->folderId;
        $folder->userId = 220;

        $I->assertTrue($folder->save());
        $I->assertEquals($this->_folders('f122')->mPath . '^' . $folder->folderId, $folder->mPath);

        $count = (new Query())->from(VFolders::tableName())->where(['userId' => 220])->count();
        $I->assertEquals(9, $count);
    }

    public function testDelete(\MyUnitTester $I)
    {
        $folder = $this->_folders('f13');
        $I->assertNotFalse($folder->delete());

        $count = (new Query())->from(VFolders::tableName())->where(['userId' => 220])->count();
        $I->assertEquals(7, $count);
        $I->assertNull(VFolders::findOne(['folderId' => $this->_folders('f13')->folderId]));

        $vfile = new VFiles();
        $vfile->folderId = $this->_folders('f1221')->folderId;
        $vfile->fileId = $this->_loadedFiles('f1')->id;
        $I->assertTrue($vfile->save());

        $folder = $this->_folders('f122');
        $I->assertNotFalse($folder->delete());

        $count = (new Query())->from(VFolders::tableName())->where(['userId' => 220])->count();
        $I->assertEquals(5, $count);

        $I->assertNull(VFolders::findOne(['folderId' => $this->_folders('f1221')->folderId]));

        $I->assertNull(LoadedFiles::findOne(['id' => $vfile->fileId]));
    }

    public function testUserRootFolder(\MyUnitTester $I)
    {
        $folder = VFolders::userRootFolder(220);
        $I->assertEquals($this->_folders('f1')->folderId, $folder->folderId);

        $folder = VFolders::userRootFolder(-1);
        $I->assertEquals($this->_folders('ff1')->folderId, $folder->folderId);
    }

    public function testAllFolders(\MyUnitTester $I)
    {
        $folders = VFolders::allFolders(220);
        $keys = array_keys($folders);

        $I->assertEquals([100, 200, 300, 400, 500, 600, 700, 800], $keys);
    }

    public function testLinks(\MyUnitTester $I)
    {
        $folder = $this->_folders('f122');

        $I->assertEquals(220, $folder->user->userId);
        $childs = $folder->childFolders;
        $I->assertCount(1, $childs);
        $I->assertEquals(600, reset($childs)->folderId);
        $I->assertEquals(300, $folder->parent->folderId);
    }

    /**
     * @param string $id
     * @return LoadedFiles
     */
    public function _loadedFiles($id)
    {
        return $this->tester->grabFixture('loadedFiles', $id);
    }

    /**
     * @param string $id
     * @return VFolders
     */
    public function _folders($id)
    {
        return $this->tester->grabFixture('folders', $id);
    }
}
