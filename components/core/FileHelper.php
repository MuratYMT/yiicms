<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.09.2015
 * Time: 10:57
 */

namespace yiicms\components\core;

class FileHelper extends \yii\helpers\FileHelper
{
    /**
     * выполняет копирование файла. заодно создает все необходимые папки в месте копирования
     * @param string $source путь к исходнму файлу
     * @param string $destination путь к файлу назначения
     * @return bool
     */
    public static function copyFile($source, $destination)
    {
        if (!static::createDirectory(pathinfo($destination, PATHINFO_DIRNAME), 0775, true)) {
            return false;
        }
        return copy($source, $destination);
    }

    /**
     * выполняет перемещение файла. заодно создает все необходимые папки в месте копирования
     * @param string $source путь к исходнму файлу
     * @param string $destination путь к файлу назначения
     * @return bool
     */
    public static function moveFile($source, $destination)
    {
        if (!static::createDirectory(pathinfo($destination, PATHINFO_DIRNAME), 0775, true)) {
            return false;
        }
        return rename($source, $destination);
    }
}
