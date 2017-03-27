<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.07.2015
 * Time: 9:25
 */

namespace yiicms\modules\users\models;

use yii\web\UploadedFile;
use yiicms\components\core\File;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Settings;

class PhotoSetForm extends AbstractProfileForm
{
    /**
     * @var UploadedFile
     */
    public $file;

    public function rules()
    {
        return [
            [
                ['file'],
                'image',
                'extensions' => implode(', ', Settings::get('core.filemanager.imageFileExtension')),
                'maxSize' => Settings::get('core.filemanager.maxFileSize'),
                'minHeight' => 100,
                'minWidth' => 100,
                'enableClientValidation' => false,
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => \Yii::t('modules/users', 'Файл фотографии'),
        ];
    }

    public function delPhoto()
    {
        $userObj = $this->user;

        $trans = $userObj::getDb()->beginTransaction();
        try {
            if (null !== $photo = $userObj->photo) {
                $userObj->photo = new File();
                if (null !== $loadedFiles = LoadedFiles::findById($photo->id)) {
                    $loadedFiles->delete();
                }
                $userObj->save();
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw new $e;
        }
    }

    /**
     * устанавливает фотографию пользователю
     * @return false|string false если файл не удалось установить и id файла если все нормально
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function setPhoto()
    {
        $this->file = UploadedFile::getInstance($this, 'file');

        if ($this->file === null || !$this->validate()) {
            $this->addError('file', \Yii::t('modules/users', 'Не удалось загрузить файл'));
            return false;
        }
        $fileLoader = new LoadedFiles();

        $userObj = $this->user;
        $trans = $userObj::getDb()->beginTransaction();
        try {
            if ($fileLoader->assign2($this->file)) {
                $userObj->photo = $fileLoader->file;
                if ($userObj->save()) {
                    $fileLoader->persistent = 1;
                    if ($fileLoader->save()) {
                        $trans->commit();
                        return true;
                    }
                }
            }
            $trans->rollBack();
            return false;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }
}
