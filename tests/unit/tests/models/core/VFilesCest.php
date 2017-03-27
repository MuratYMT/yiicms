<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 8:30
 */

namespace common\unit\test\models\web;

use yiicms\models\core\LoadedFiles;
use yiicms\models\core\VFiles;
use yiicms\models\core\VFolders;
use yiicms\tests\_data\fixtures\models\core\LoadedFilesFixture;
use yiicms\tests\_data\fixtures\models\core\VFilesFixture;
use yiicms\tests\_data\fixtures\models\core\VFoldersFixture;
use tests\unit\UnitCest;

/**
 * Class VFilesCest
 * @package tests\unit\test\models\web
 *
 * @method LoadedFiles loadedFiles($id)
 * @method VFolders folders($id)
 */
class VFilesCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'vfiles' => VFilesFixture::className(),
            'loadedFiles' => LoadedFilesFixture::className(),
            'folders' => VFoldersFixture::className(),
        ];
    }

    public function testLink(\MyUnitTester $I)
    {
        $loaded = $this->_loadedFiles('f1');
        $folder = $this->_folders('f1');

        $vfiles = new VFiles();
        $vfiles->folderId = $folder->folderId;
        $vfiles->fileId = $loaded->id;

        $I->assertTrue($vfiles->save());

        $files = $folder->vFiles;
        $I->assertCount(1, $files);
        $I->assertEquals($loaded->id, reset($files)->fileId);
        $I->assertEquals($folder->folderId, $vfiles->vFolder->folderId);
    }

    public function testDelete(\MyUnitTester $I)
    {
        $loaded = $this->_loadedFiles('f1');
        $folder = $this->_folders('f1');
        $vfiles = new VFiles();
        $vfiles->folderId = $folder->folderId;
        $vfiles->fileId = $loaded->id;
        $vfiles->save();

        $I->assertFileExists(\Yii::getAlias('@uploadFolder/' . $loaded->path));
        $I->assertNotNull(LoadedFiles::findOne(['id' => $loaded->id]));

        $vfiles->delete();
        $I->assertFileNotExists(\Yii::getAlias('@uploadFolder/' . $loaded->path));
        $I->assertNull(LoadedFiles::findOne(['id' => $loaded->id]));

        $files = $folder->vFiles;
        $I->assertCount(0, $files);
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
