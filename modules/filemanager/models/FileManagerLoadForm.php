<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 04.09.2015
 * Time: 8:30
 */

namespace yiicms\modules\filemanager\models;

use yiicms\components\core\FileLoadForm;
use yiicms\components\YiiCms;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Users;
use yiicms\models\core\VFiles;
use yiicms\models\core\VFolders;

class FileManagerLoadForm extends FileLoadForm
{
    /** @var int В какую папку загружать */
    public $folderId;

    public function upload(Users $user)
    {
        $trans = VFolders::getDb()->beginTransaction();
        try {
            $files = parent::upload($user);
            if ($files === false) {
                return false;
            }
            $loadedFileService = YiiCms::$app->loadedFileService;
            /** @var LoadedFiles[] $files */
            foreach ($files as $file) {
                $file->persistent = 1;

                $vfile = new VFiles(['fileId' => $file->id, 'folderId' => $this->folderId]);

                if (!$vfile->save() || !$loadedFileService->save($file)) {
                    $this->addError('files', \Yii::t('modules/filemanager', 'Файлы не загружены'));
                    $trans->rollBack();
                    return false;
                }
            }

            $trans->commit();
            return $files;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }
}
