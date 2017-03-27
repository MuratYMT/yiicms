<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.05.2016
 * Time: 8:18
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\components\core\FileHelper;
use yiicms\models\core\LoadedFiles;
use yiicms\tests\_data\ActiveFixture;
use yii\web\UploadedFile;

class LoadedFilesFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = LoadedFiles::className();
        $this->depends = [UsersFixture::className()];
        parent::__construct($config);
    }

    public function load()
    {
        parent::load();
        $data = $this->getData();
        $tmpDir = sys_get_temp_dir();
        $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
        $uploadFolder = \Yii::getAlias('@uploadFolder/');
        foreach ($data as $file) {
            //FileHelper::copyFile($sourcePath . $file['title'], $tmpDir . DIRECTORY_SEPARATOR . $file['path']);
            FileHelper::copyFile($sourcePath . $file['title'], $uploadFolder . $file['path']);
        }

        FileHelper::copyFile($sourcePath . 'Chrysanthemum.jpg', $tmpDir . DIRECTORY_SEPARATOR . 'a1a1.jpg');
    }

    public function unload()
    {
        try {
            $data = $this->getData();
            $tmpDir = sys_get_temp_dir();
            $uploadFolder = \Yii::getAlias('@uploadFolder/');
            foreach ($data as $file) {
                //unlink($tmpDir . DIRECTORY_SEPARATOR . $file['path']);
                @unlink($uploadFolder . $file['path']);
            }

            @unlink($tmpDir . DIRECTORY_SEPARATOR . 'a1a1.jpg');
        } catch (\Exception $e) {
        }

        parent::unload();
    }

    /**
     * создает объект класса UploadFile
     * @return UploadedFile
     */
    public static function getUpload()
    {
        $upload = new UploadedFile();
        $upload->tempName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'a1a1.jpg';
        $upload->name = 'Chrysanthemum.jpg';
        $upload->type = 'image/jpeg';
        $upload->size = filesize(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'a1a1.jpg');
        return $upload;
    }
}
