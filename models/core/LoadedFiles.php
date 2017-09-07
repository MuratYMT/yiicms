<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yiicms\components\core\behavior\TimestampBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\File;
use yiicms\components\core\validators\TitleFilter;
use yiicms\components\YiiCms;

/**
 * This is the model class for table "web.loadedFiles".
 * @property string $id Идентификатор файла
 * @property string $path Относительный URL файла
 * @property string $title Оригинальное имя файла
 * @property string|DateTime $createdAt Время загрузки файла. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном
 * в \Yii::$app->formatter->timeZone либо объект DateTime
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
     * выполняет копирование файла из временной папки в папку @uploadFolder и сохраняет описание файла
     * в базу временных файлов
     * @param UploadedFile $file
     * @param Users $user
     * @return bool
     */
    public function assign2(UploadedFile $file, Users $user)
    {
        $this->id = \Yii::$app->security->generateRandomString();
        $path = YiiCms::$app->loadedFileService->moveToUpload($this->id, $file->tempName, $file->extension);
        if (false === $path) {
            return false;
        }

        $this->path = $path;
        $this->title = $file->baseName . '.' . $file->extension;
        $this->userId = $user->userId;
        $this->size = filesize(\Yii::getAlias('@uploadFolder/') . $path);

        $this->persistent = 0;

        return true;
    }

    // ------------------------------------------------- связи --------------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'userId']);
    }

    // -------------------------------------------- геттеры и сеттеры -------------------------------------------------
    public function getFile()
    {
        return new File(['id' => $this->id, 'path' => $this->path, 'title' => $this->title, 'public' => $this->public]);
    }
}
