<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.05.2016
 * Time: 8:39
 */

namespace common\unit\test\models\web;

use Imagine\Image\ManipulatorInterface;
use yiicms\components\YiiCms;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Users;
use yiicms\tests\_data\fixtures\models\core\LoadedFilesFixture;
use tests\unit\UnitCest;

/**
 * Class LoadedFilesCest
 * @package tests\unit\test\models\web
 * @method LoadedFiles files($key)
 */
class LoadedFilesCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return ['loadedFiles' => LoadedFilesFixture::className()];
    }

    public function testAssign(\MyUnitTester $I)
    {
        $upload = LoadedFilesFixture::getUpload();
        $user = Users::findById(220);
        $loaded = new LoadedFiles();
        $loaded->assign2($upload, $user);

        $I->assertTrue(YiiCms::$app->loadedFileService->save($loaded));

        $I->assertEquals(0, $loaded->persistent);
        $I->assertFileExists(\Yii::getAlias('@uploadFolder/' . $loaded->path));

        $user = Users::findById(220);
        $I->assertEquals($loaded->size, $user->uploadedFilesSize);

        YiiCms::$app->loadedFileService->delete($loaded);
        $I->assertFileNotExists(\Yii::getAlias('@uploadFolder/' . $loaded->path));

        $user = Users::findById(220);
        $I->assertEquals(0, $user->uploadedFilesSize);
    }

    public function testFindByPath(\MyUnitTester $I)
    {
        $f2 = $this->_files('f2');

        $file = LoadedFiles::findByPath($f2->path);
        $I->assertNotNull($file);
        $I->assertEquals($f2->id, $file->id);
    }

    public function testFindById(\MyUnitTester $I)
    {
        $f2 = $this->_files('f2');

        $file = LoadedFiles::findById($f2->id);
        $I->assertNotNull($file);
        $I->assertEquals($f2->path, $file->path);
    }

    public function testPublishFile(\MyUnitTester $I)
    {
        $loadedFileService = YiiCms::$app->loadedFileService;
        $f2 = $this->_files('f2');
        $path = pathinfo($f2->path, PATHINFO_DIRNAME) . '/';
        $file = pathinfo($f2->path, PATHINFO_FILENAME) . '.' . pathinfo($f2->path, PATHINFO_EXTENSION);
        $result = $loadedFileService->publishFile($path, $file);

        $I->assertNotEquals(-1, $result);
        $I->assertNotFalse($result);

        $I->assertFileExists(\Yii::getAlias('@upload/' . $f2->path));

        $loadedFileService->delete($f2);
        $I->assertFileNotExists(\Yii::getAlias('@upload/' . $f2->path));
        $I->assertFileNotExists(\Yii::getAlias('@uploadFolder/' . $f2->path));
    }

    public function testPublishThumbNail(\MyUnitTester $I)
    {
        $loadedFileService = YiiCms::$app->loadedFileService;
        $f2 = $this->_files('f2');
        $path = pathinfo($f2->path, PATHINFO_DIRNAME) . '/';
        $file = pathinfo($f2->path, PATHINFO_FILENAME) . '.' . pathinfo($f2->path, PATHINFO_EXTENSION);
        $result = $loadedFileService->publishThumbnail($path, $file, 100, 100, ManipulatorInterface::THUMBNAIL_INSET);

        $I->assertNotEquals(-1, $result);
        $I->assertNotFalse($result);

        $I->assertFileExists(\Yii::getAlias('@upload/'
            . $loadedFileService->makeThumbnailPath($f2->path, 100, 100, ManipulatorInterface::THUMBNAIL_INSET)));

        $loadedFileService->delete($f2);
        $I->assertFileNotExists(\Yii::getAlias('@upload/'
            . $loadedFileService->makeThumbnailPath($f2->path, 100, 100, ManipulatorInterface::THUMBNAIL_INSET)));
    }

    /**
     * @param string $id
     * @return LoadedFiles
     */
    public function _files($id)
    {
        return $this->tester->grabFixture('loadedFiles', $id);
    }
}
