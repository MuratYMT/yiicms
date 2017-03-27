<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 20.04.2015
 * Time: 9:27
 */

namespace yiicms\components\users;

use yiicms\components\core\rbac\Rule;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\VFolders;
use yii\base\InvalidConfigException;

/**
 * Class FileManagerOwnRule правило определяющее является ли текущий пользователь владельцем файла или папки
 * @package sfw\modules\rbac
 */
class FileManagerOwnRule extends Rule
{
    /**
     * @inheritDoc
     */
    public function execute($user, $item, $params)
    {
        if (array_key_exists('fileId', $params)) {
            $obj = LoadedFiles::findById($params['fileId']);
        } elseif (array_key_exists('folderId', $params)) {
            $obj = VFolders::findOne(['folderId' => $params['folderId']]);
        } else {
            throw new InvalidConfigException('Need set fileId or folderId before use this');
        }
        if ($obj === null) {
            return false;
        }
        return (int)$user === (int)$obj->userId;
    }
}
