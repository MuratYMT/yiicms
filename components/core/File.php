<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18.05.2016
 * Time: 8:29
 */

namespace yiicms\components\core;

use Imagine\Image\ManipulatorInterface;
use yii\base\Object;
use yii\web\View;
use yiicms\components\core\yii\Theme;
use yiicms\components\YiiCms;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Settings;

/**
 * Class File
 * @package yiicms\components\core
 * @property bool $isEmpty флаг пустого файла
 * @property bool $isImage флаг того что содержимое это картинка
 * @property LoadedFiles $loadedFile
 */
class File extends Object
{
    /** @var  string Идентификатор файла */
    public $id;
    /** @var  string Относительный URL файла */
    public $path;
    /** @var  string Оригинальное имя файла */
    public $title;

    /** @var string имя файла в папке [[Theme::imgBaseUrl]] который отображается если объект пустой */
    public $noIcon = 'no-icon.png';

    /** @var bool видимость файла */
    public $public = true;

    public function asArray()
    {
        return ['id' => $this->id, 'path' => $this->path, 'title' => $this->title, 'public' => $this->public];
    }

    /**
     * создает ссылку на предпросмотр указанного размера
     * @param View $view
     * @param int $width ширина требуемой превьюхи
     * @param int $height высота требуемой превьюхи
     * @param string $style стиль отображения (уместить в размеры, обрезать в размер)
     * @return string ссылка для загрузки файла предпросмотра
     * @throws \yii\base\InvalidParamException
     */
    public function asThumbnail($view, $width, $height, $style = ManipulatorInterface::THUMBNAIL_INSET)
    {
        return YiiCms::$app->loadedFileService->thumbnailLink($view, $this->path, $width, $height, $style);
    }

    /**
     * отобразить содержимое как URL по которому будет доступен файл как фотография
     * @param View $view
     * @return string
     */
    public function asPhotoUrl($view)
    {
        if ($this->getIsEmpty()) {
            /** @var $theme Theme */
            $theme = $view->theme;
            return $theme->imgBaseUrl . '/' . $this->noIcon;
        }

        return \Yii::getAlias('@webupload' . '/' . $this->path);
    }

    /**
     * создает объект File из json строки.
     * Если в json содержится двумерный массив то создается массив из объектов File
     * @param string $json
     * @return File[]
     */
    public static function createFromJson($json)
    {
        /** @var array $array */
        $array = @json_decode($json, true);
        if (!is_array($array)) {
            return [];
        }
        $result = [];
        foreach ($array as $row) {
            $obj = new File($row);
            $result[$obj->id] = $obj;
        }
        return $result;
    }

    /**
     * преобразует файлы в json строку
     * @param File|File[] $value
     * @return string
     */
    public static function saveToJson($value)
    {
        $value = ArrayHelper::asArray($value);

        $result = [];
        foreach ($value as $file) {
            /** @var  self $file */
            $result[$file->id] = $file->asArray();
        }
        return json_encode($result, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
    }

    // ------------------------------------------------------- геттеры и сеттеры -----------------------------------------------------------

    public function getIsImage()
    {
        if ($this->getIsEmpty()) {
            return false;
        }

        return in_array(pathinfo($this->path, PATHINFO_EXTENSION), Settings::get('core.filemanager.imageFileExtension'), true);
    }

    public function getIsEmpty()
    {
        return (empty($this->id) || (empty($this->path) || empty($this->title)));
    }

    public function getLoadedFile()
    {
        return $this->getIsEmpty() ? null : LoadedFiles::findOne(['id' => $this->id]);
    }
}
