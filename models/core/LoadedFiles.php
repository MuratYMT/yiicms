<?php

namespace yiicms\models\core;

use Imagine\Image\Box;
use yii\db\ActiveRecord;
use yii\imagine\Image;
use yii\web\UploadedFile;
use yii\web\View;
use yiicms\components\core\behavior\TimestampBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\File;
use yiicms\components\core\FileHelper;
use yiicms\components\core\fileicons\IconsAsset;
use yiicms\components\core\validators\TitleFilter;

/**
 * This is the model class for table "web.loadedFiles".
 * @property string $id Идентификатор файла
 * @property string $path Относительный URL файла
 * @property string $title Оригинальное имя файла
 * @property string|DateTime $createdAt Время загрузки файла. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property integer $size Размер файла
 * @property integer $persistent Флаг того что файл временный
 * @property integer $userId ID загрузившего пользователя
 * @property integer $public флаг доступного файла
 * @property Users $user
 * @property File $file
 */
class LoadedFiles extends ActiveRecord
{
    /**
     * разделитель ширины и высоты в пути к файлу прелпросмотра
     */
    const SIZE_DELIMITER = 'xXx';

    const SC_RENAME = 'rename';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loadedFiles}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => TimestampBehavior::class,
                'createdAttributes' => ['createdAt'],
            ],
        ]);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_RENAME => ['title'],
        ];
    }

    public function isTransactional($operation)
    {
        return true;
    }

    public function init()
    {
        parent::init();
        if ($this->persistent === null) {
            $this->persistent = 0;
        }
        if ($this->public === null) {
            $this->public = 1;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'path', 'title', 'userId', 'size', 'persistent', 'public'], 'required'],
            [['size', 'persistent', 'userId'], 'integer'],
            [['id'], 'string', 'max' => 64],
            [['path', 'title'], 'string', 'max' => 255],
            [['title'], TitleFilter::class],
            [['userId'], 'exist', 'targetClass' => Users::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('yiicms', 'Идентификатор файла'),
            'path' => \Yii::t('yiicms', 'Относительный URL файла'),
            'title' => \Yii::t('yiicms', 'Оригинальное имя файла'),
            'createdAt' => \Yii::t('yiicms', 'Время загрузки файла'),
            'size' => \Yii::t('yiicms', 'Размер файла'),
            'persistent' => \Yii::t('yiicms', 'Флаг того что файл временный'),
            'userId' => \Yii::t('yiicms', 'ID загрузившего пользователя'),
            'public' => \Yii::t('yiicms', 'Файл доступен для просмотра'),
        ];
    }

    /**
     * @param string $id идентификатор файла
     * @return null|LoadedFiles
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @param string $path путь файла
     * @return LoadedFiles|null
     */
    public static function findByPath($path)
    {
        return static::find()->where(['path' => $path])->one();
    }

    /**
     * выполняет копирование файла из временной папки в папку @uploadFolder и сохраняет описание файла в базу временных файлов
     * @param string $tempPath путь к файлу во временной папке
     * @param string $fileName оригинальное имя файлы
     * @param int $userId кто загрузил по умолчанию текущий пользователь
     * @return bool
     * @deprecated
     * @TODO удалить этот метод после первой миграции
     */
    public function assign($tempPath, $fileName, $userId = null)
    {
        $this->id = \Yii::$app->security->generateRandomString();

        if (false === $path = $this->moveToUpload($this->id, $tempPath, pathinfo($fileName, PATHINFO_EXTENSION))) {
            return false;
        }

        $this->path = $path;
        $this->title = $fileName;
        $this->userId = $userId === null ? \Yii::$app->user->id : $userId;
        $this->size = filesize(\Yii::getAlias('@uploadFolder/') . $path);

        $this->persistent = 0;

        return true;
    }

    /**
     * выполняет копирование файла из временной папки в папку @uploadFolder
     * @param UploadedFile $file
     * @param int $userId
     * @return bool
     */
    public function assign2($file, $userId = null)
    {
        $this->id = \Yii::$app->security->generateRandomString();

        if (false === $path = $this->moveToUpload($this->id, $file->tempName, $file->extension)) {
            return false;
        }

        $this->path = $path;
        $this->title = $file->baseName . '.' . $file->extension;
        $this->userId = $userId === null ? \Yii::$app->user->id : $userId;
        $this->size = filesize(\Yii::getAlias('@uploadFolder/') . $path);

        $this->persistent = 0;

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->user->addUploadedFilesSize($this->size);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        //удаляем из папки загрузки
        self::deleteFilesFromDisk(\Yii::getAlias('@uploadFolder/' . $this->path));
        //удаляем из публичной папки
        self::deleteFilesFromDisk(\Yii::getAlias('@upload/' . $this->path));

        $this->user->addUploadedFilesSize(-$this->size);
    }

    private static function deleteFilesFromDisk($fileName)
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
     * генерирует файл предпросмотра
     * @param string $path относительный путь до папки с файлом на диске
     * @param string $fileName имя файла картинки для которого надо сформировать предпросмотр
     * @param int $width максимальная ширина предпросмотра
     * @param int $height максимальная высота предпросмотра
     * @param string $style стиль отображения (уместить в размеры, обрезать в размер)
     * @return string путь к фалу предпросмотра
     */
    public static function publishThumbnail($path, $fileName, $width, $height, $style)
    {
        $sourceFile = \Yii::getAlias('@uploadFolder/') . $path . $fileName;
        $thumbnailFile = \Yii::getAlias('@upload/') . static::makeThumbnailPath($path . $fileName, $width, $height, $style);

        if (!file_exists($sourceFile)) {
            return -1;
        }

        if (!FileHelper::createDirectory(pathinfo($thumbnailFile, PATHINFO_DIRNAME), 0775, true)) {
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
    public static function publishFile($path, $fileName)
    {
        $uploadedFile = \Yii::getAlias('@uploadFolder/') . $path . $fileName;
        $publicFile = \Yii::getAlias('@upload/') . $path . $fileName;

        if (!file_exists($uploadedFile)) {
            return -1;
        }

        if (!FileHelper::createDirectory(pathinfo($publicFile, PATHINFO_DIRNAME), 0775, true)) {
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
    public static function thumbnailLink($view, $filePath, $width, $height, $style)
    {
        $iconAsset = IconsAsset::register($view);

        $imgType = implode('|', Settings::get('core.filemanager.imageFileExtension'));

        if (preg_match('/\.(' . $imgType . ')$/', $filePath)) {
            return \Yii::getAlias('@webupload/') . static::makeThumbnailPath($filePath, $width, $height, $style);
        } else {
            //не картинка
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $iconPath = $iconAsset->basePath . '/' . $ext . '.png';

            if (file_exists($iconPath)) {
                return $iconAsset->baseUrl . '/' . $ext . '.png';
            } else {
                return $iconAsset->baseUrl . '/default.png';
            }
        }
    }

    /**
     * генерирует относительный путь к файлу предпросмотра
     * @param string $filePath относительный путь к исходному файлу
     * @param int $width ширина требуемой превьюхи
     * @param int $height высота требуемой превьюхи
     * @param string $style стиль отображения (уместить в размеры, обрезать в размер)
     * @return string
     */
    public static function makeThumbnailPath($filePath, $width, $height, $style)
    {
        return $filePath . '_' . $width . self::SIZE_DELIMITER . $height . '_' . $style . '.png';
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
    private function moveToUpload($id, $sourcePath, $extension)
    {
        $fname = $this->id2FileName($id) . '.' . $extension;
        //@TODO после первичной миграции заменить FileHelper::copyFile() на self::moveToUpload()
        if (FileHelper::copyFile($sourcePath, \Yii::getAlias('@uploadFolder/' . $fname))) {
            return $fname;
        }

        return false;
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

    // ------------------------------------------------- связи ----------------------------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'userId']);
    }

    // -------------------------------------------- геттеры и сеттеры ----------------------------------------------------------------
    public function getFile()
    {
        return new File(['id' => $this->id, 'path' => $this->path, 'title' => $this->title, 'public' => $this->public]);
    }
}
