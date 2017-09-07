<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18.05.2016
 * Time: 15:24
 */

namespace yiicms\components\core;

use yii\base\Model;
use yii\web\UploadedFile;
use yiicms\components\YiiCms;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Settings;
use yiicms\models\core\Users;

class FileLoadForm extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $uFiles;

    /**
     * @var bool использовать асинхронную загрузку файлов на сервер
     */
    public $uploadAsync = true;

    /**
     * @var int максимальное количество загружаемых за один раз файлов
     */
    public $maxFiles = 20;

    /**
     * @var bool использовать валидацию на стороне клиента
     */
    public $clientValidation = false;

    public function rules()
    {
        return [
            [
                ['uFiles'],
                'file',
                'extensions' => implode(', ', self::allowedExtension()),
                'maxSize' => self::maxFileSize(),
                'enableClientValidation' => $this->clientValidation,
                'maxFiles' => $this->maxFiles
            ],
        ];
    }

    /**
     * массив расширений файлов доступных к загрузке
     * @return string[]
     */
    public static function allowedExtension()
    {
        return array_merge(
            Settings::get('core.filemanager.imageFileExtension'),
            Settings::get('core.filemanager.fileExtension')
        );
    }

    /**
     * максимальный размер одного файла в байтах
     * @return int
     */
    public static function maxFileSize()
    {
        return Settings::get('core.filemanager.maxFileSize');
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'uFiles' => \Yii::t('yiicms', 'Загрузка файлов'),
        ];
    }

    /**
     * Выполняет загрузку файлов. Копирует файл в папку загрузок и сохраняет запись в базу данных.
     * Файл имеет временный флаг
     * @param Users $user
     * @return false|LoadedFiles[]
     * @throws \Exception
     */
    public function upload(Users $user)
    {
        $this->uFiles = UploadedFile::getInstances($this, 'uFiles');

        if ($this->uFiles === null || !$this->validate()) {
            if (!$this->hasErrors('uFiles')) {
                $this->addError('uFiles', \Yii::t('yiicms', 'Не удалось загрузить файл'));
            }
            return false;
        }
        $trans = LoadedFiles::getDb()->beginTransaction();
        try {
            $result = [];
            $loadedFileService = YiiCms::$app->loadedFileService;
            foreach ($this->uFiles as $file) {
                $loadedFile = new LoadedFiles();
                if (!$loadedFile->assign2($file, $user) || !$loadedFileService->save($loadedFile)) {
                    $this->addError('uFiles', \Yii::t('yiicms', 'Файлы не загружены'));
                    $trans->rollBack();
                    return false;
                }
                $result[] = $loadedFile;
            }

            $trans->commit();
            return $result;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }
}
