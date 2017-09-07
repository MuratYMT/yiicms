<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.09.2017
 * Time: 23:43
 */

namespace yiicms\services;

use Imagine\Image\Box;
use yii\imagine\Image;
use yii\web\View;
use yiicms\components\core\FileHelper;
use yiicms\components\core\fileicons\IconsAsset;
use yiicms\components\YiiCms;
use yiicms\models\core\constants\LoadedFileConst;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Settings;

class LoadedFileService
{
    public function save(LoadedFiles $loadedFile)
    {
        $isNew = $loadedFile->isNewRecord;
        $transaction = YiiCms::$app->db->beginTransaction();
        try {
            $result = $loadedFile->save();
            if ($result) {
                if ($isNew) {
                    $loadedFile->user->addUploadedFilesSize($loadedFile->size);
                }
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function delete(LoadedFiles $loadedFile)
    {
        $trans = YiiCms::$app->db->beginTransaction();

        try {
            $result = $loadedFile->delete();
            if (false === $result) {
                $trans->rollBack();
                return false;
            }
            //удаляем из папки загрузки
            $this->deleteFilesFromDisk(\Yii::getAlias('@uploadFolder/' . $loadedFile->path));
            //удаляем из публичной папки
            $this->deleteFilesFromDisk(\Yii::getAlias('@upload/' . $loadedFile->path));

            if (!$loadedFile->user->addUploadedFilesSize(-$loadedFile->size)) {
                $trans->rollBack();
                return false;
            }
            $trans->commit();
            return $result;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * генерирует файл предпросмотра
     * @param string $path относительный путь до папки с файлом на диске
     * @param string $fileName имя файла картинки для которого надо сформировать предпросмотр
     * @param int $width максимальная ширина предпросмотра
     * @param int $height максимальная высота предпросмотра
     * @param string $style стиль отображения (уместить в размеры, обрезать в размер)
     * @return string путь к фалу предпросмотра
     */
    public function publishThumbnail($path, $fileName, $width, $height, $style)
    {
        $sourceFile = \Yii::getAlias('@uploadFolder/') . $path . $fileName;
        $thumbnailFile = \Yii::getAlias('@upload/')
            . $this->makeThumbnailPath($path . $fileName, $width, $height, $style);

        if (!file_exists($sourceFile)) {
            return -1;
        }

        if (!FileHelper::createDirectory(pathinfo($thumbnailFile, PATHINFO_DIRNAME))) {
            return false;
        }

        $image = Image::getImagine()->open($sourceFile);
        $size = $image->getSize();
        if (empty($width)) {
            $width = $size->getWidth();
        }
        if (empty($height)) {
            $height = $size->getHeight();
        }
        $image->thumbnail(new Box($width, $height), $style)->save($thumbnailFile);

        return $thumbnailFile;
    }

    /**
     * выполняет копирование файла из папки загрузки в папку доступную из интеренета
     * @param string $path относительный путь до папки с файлом на диске
     * @param string $fileName имя файла
     * @return string путь к фалу в публичной папке
     */
    public function publishFile($path, $fileName)
    {
        $uploadedFile = \Yii::getAlias('@uploadFolder/') . $path . $fileName;
        $publicFile = \Yii::getAlias('@upload/') . $path . $fileName;

        if (!file_exists($uploadedFile)) {
            return -1;
        }

        if (!FileHelper::createDirectory(pathinfo($publicFile, PATHINFO_DIRNAME))) {
            return false;
        }

        FileHelper::copyFile($uploadedFile, $publicFile);

        return $publicFile;
    }

    /**
     * создает ссылку на предпросмотр указанного размера
     * @param View $view
     * @param string $filePath имя файла для которого нужен предпросмотр
     * @param int $width ширина требуемой превьюхи
     * @param int $height высота требуемой превьюхи
     * @return string ссылка для загрузки файла предпросмотра
     * @param string $style стиль отображения (уместить в размеры, обрезать в размер)
     * @throws \yii\base\InvalidParamException
     */
    public function thumbnailLink($view, $filePath, $width, $height, $style)
    {
        $iconAsset = IconsAsset::register($view);

        $imgType = implode('|', Settings::get('core.filemanager.imageFileExtension'));

        if (preg_match('/\.(' . $imgType . ')$/', $filePath)) {
            return \Yii::getAlias('@webupload/') . $this->makeThumbnailPath($filePath, $width, $height, $style);
        }

        //не картинка
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $iconPath = $iconAsset->basePath . '/' . $ext . '.png';

        if (file_exists($iconPath)) {
            return $iconAsset->baseUrl . '/' . $ext . '.png';
        }
        return $iconAsset->baseUrl . '/default.png';
    }

    /**
     * перемещает файл из временной папки в папку upload
     * @param string $id идентификатор файла
     * @param string $sourcePath откуда копировать
     * @param string $extension Расширение оригинального файла
     * @return false|string относительный путь к файлу в папке загрузки
     * false если не удалось переметить файл
     * @throws \yii\base\InvalidParamException
     */
    public function moveToUpload($id, $sourcePath, $extension)
    {
        $fileName = $this->id2FileName($id) . '.' . $extension;
        if (FileHelper::moveFile($sourcePath, \Yii::getAlias('@uploadFolder/' . $fileName))) {
            return $fileName;
        }

        return false;
    }

    /**
     * генерирует относительный путь к файлу предпросмотра
     * @param string $filePath относительный путь к исходному файлу
     * @param int $width ширина требуемой превьюхи
     * @param int $height высота требуемой превьюхи
     * @param string $style стиль отображения (уместить в размеры, обрезать в размер)
     * @return string
     */
    public function makeThumbnailPath($filePath, $width, $height, $style)
    {
        return $filePath . '_' . $width . LoadedFileConst::SIZE_DELIMITER . $height . '_' . $style . '.png';
    }

    private function deleteFilesFromDisk($fileName)
    {
        $dir = pathinfo($fileName, PATHINFO_DIRNAME);
        if (!is_dir($dir)) {
            return;
        }
        $baseFileName = pathinfo($fileName, PATHINFO_FILENAME);
        $handle = opendir($dir);
        while (false !== $file = readdir($handle)) {
            if (strpos($file, $baseFileName) === 0) {
                unlink($dir . '/' . $file);
            }
        }
        closedir($handle);
    }

    /**
     * формирует из строки вида Gr9-utIkfERg путь к Файлу в виде G/r/9-utIkfERg
     * @param string $id id файла
     * @param string $namePrefix префикс имени
     * @return string имя файла
     */
    private function id2FileName($id, $namePrefix = '')
    {
        $depth = Settings::get('core.speed.uploadStructureDepth');
        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $depth; $i++) {
            /** @noinspection OffsetOperationsInspection */
            $path[] = $id{$i}; // substr($key, $i, 1);
        }
        $path[] = $namePrefix . mb_substr($id, $depth, 32 - $depth);

        return implode('/', $path);
    }
}
