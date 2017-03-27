<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 04.09.2015
 * Time: 8:09
 */

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class VFiles
 * @package yiicms\models\web
 * @property int $folderId ID виртуального каталога
 * @property string $fileId ID файла
 * @property VFolders $vFolder
 * @property LoadedFiles $loadedFile
 */
class VFiles extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%vFiles}}';
    }

    /**
     * @inheritdoc
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function rules()
    {
        return [
            [['folderId'], 'integer'],
            [['folderId', 'fileId'], 'required'],
            [['folderId'], 'exist', 'targetClass' => VFolders::class],
            [['fileId'], 'string', 'max' => 64],
            [['fileId'], 'exist', 'targetClass' => LoadedFiles::class, 'targetAttribute' => ['fileId' => 'id']],
        ];
    }

    public function afterDelete()
    {
        $this->loadedFile->delete();
        parent::afterDelete();
    }

    // ------------------------------------------------------ связи -------------------------------------------------------------------

    public function getVFolder()
    {
        return $this->hasOne(VFolders::class, ['folderId' => 'folderId']);
    }

    public function getLoadedFile()
    {
        return $this->hasOne(LoadedFiles::class, ['id' => 'fileId']);
    }
}
